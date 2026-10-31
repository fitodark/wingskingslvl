<?php

namespace App\Services;

use App\Client;
use App\TarifaEnvio;
use App\Venta;

/**
 * Tarifa de envío según la colonia (catálogo tarifas_envio). La usan el paso 1 de la
 * comanda (consulta y guardado) y el formulario de clientes para validar la colonia.
 */
class EnvioService
{
    /**
     * Colonia activa del catálogo. La collation utf8mb4_unicode_ci ignora mayúsculas
     * y acentos, así que "acatlima" encuentra "Acatlima".
     */
    public function buscarTarifa($colonia)
    {
        $colonia = trim((string) $colonia);
        if ($colonia === '') {
            return null;
        }
        return TarifaEnvio::activas()->where('colonia', $colonia)->first();
    }

    /**
     * Lo que necesita el paso 1 para pintar el panel de envío.
     */
    public function opciones(TarifaEnvio $tarifa)
    {
        return [
            'encontrada' => true,
            'colonia' => $tarifa->colonia,
            'envio_gratuito' => $tarifa->envio_gratuito,
            // En colonia gratuita el cliente siempre puede pedir express; si no hay tarifa,
            // el monto se acuerda por teléfono fuera del sistema (monto_express null).
            'express_disponible' => $tarifa->envio_gratuito || $tarifa->monto_express !== null,
            'monto_express' => $tarifa->monto_express,
        ];
    }

    /**
     * Envío que se guarda al pasar el paso 1. Devuelve ['error' => texto|null, 'campos' => array|null];
     * campos null = no tocar los de la venta.
     * - Mesa y Para llevar: los tres campos en NULL.
     * - Venta del agente que ya trae envío y conserva el cliente: se respeta lo aceptado por WhatsApp.
     * - Domicilio: según la colonia del cliente; el monto sale del catálogo.
     *   Única excepción: express pedido por el cliente en colonia gratuita sin tarifa; ese monto
     *   se acuerda por teléfono y se captura en el paso 1 (obligatorio).
     *
     * @param  string|null  $envio  'gratuito' | 'express' (radio del paso 1)
     * @param  bool  $aceptaExpress  el cliente aceptó el express de una colonia que solo tiene express
     * @param  mixed  $montoAcordado  monto capturado en el paso 1 (solo cuenta en el caso de arriba)
     */
    public function envioDeVenta(Venta $venta, $tipo, $clientId, $envio, $aceptaExpress, $montoAcordado = null)
    {
        $sinEnvio = ['tipo_envio' => null, 'monto_envio_express' => null, 'colonia_envio' => null];
        if ((int) $tipo !== Venta::TIPO_DOMICILIO) {
            return ['error' => null, 'campos' => $sinEnvio];
        }
        if ($venta->venta_agente && $venta->tipo_envio !== null && (string) $venta->client_id === (string) $clientId) {
            return ['error' => null, 'campos' => null];
        }

        $cliente = Client::find($clientId);
        if ($cliente === null) {
            return ['error' => null, 'campos' => null]; // el validador ya avisa que falta el cliente
        }
        $tarifa = $this->buscarTarifa($cliente->colonia);
        if ($tarifa === null) {
            return ['error' => 'La dirección del cliente no tiene una colonia del catálogo de envío.', 'campos' => null];
        }

        if (!$tarifa->envio_gratuito) {
            if (!$aceptaExpress) {
                return ['error' => 'El cliente debe aceptar el envío express para continuar.', 'campos' => null];
            }
            $express = true;
        } else {
            $express = $envio === 'express';
        }
        // Una colonia solo express sin tarifa es un error del catálogo.
        if ($express && !$tarifa->envio_gratuito && $tarifa->monto_express === null) {
            return ['error' => 'Esta colonia no tiene tarifa express.', 'campos' => null];
        }
        $monto = $express ? $tarifa->monto_express : null;
        // Colonia gratuita sin tarifa express: el monto acordado por teléfono es obligatorio.
        if ($express && $tarifa->envio_gratuito && $tarifa->monto_express === null) {
            $texto = trim((string) $montoAcordado);
            if ($texto === '') {
                return ['error' => 'Escribe el monto express acordado con el cliente.', 'campos' => null];
            }
            if (!is_numeric($texto) || $texto <= 0 || $texto > 9999.99) {
                return ['error' => 'El monto express debe ser mayor a 0 y hasta $ 9,999.99.', 'campos' => null];
            }
            $monto = round((float) $texto, 2);
        }

        return ['error' => null, 'campos' => [
            'tipo_envio' => $express ? Venta::ENVIO_EXPRESS : Venta::ENVIO_GRATUITO,
            'monto_envio_express' => $monto,
            'colonia_envio' => $tarifa->colonia,
        ]];
    }

    /**
     * Respuesta del endpoint del paso 1 para la colonia de un cliente.
     */
    public function respuesta($colonia)
    {
        $tarifa = $this->buscarTarifa($colonia);
        if ($tarifa === null) {
            $colonia = trim((string) $colonia);
            return ['encontrada' => false, 'colonia' => $colonia === '' ? null : $colonia];
        }
        return $this->opciones($tarifa);
    }
}
