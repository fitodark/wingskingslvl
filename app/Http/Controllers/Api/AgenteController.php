<?php

namespace App\Http\Controllers\Api;

use App\Venta;
use App\Http\Controllers\Controller;
use App\Services\VentaService;

/**
 * Servicios para el agente de WhatsApp. El agente inserta las ventas directo en MySQL
 * (venta_agente = 1) y después llama aquí con el folio para imprimir la orden.
 * La autorización la hace el middleware agente.token (encabezado X-Agente-Token).
 */
class AgenteController extends Controller
{

    /**
     * POST /api/agente/ventas/{folio}/imprimir-orden
     * Imprime las órdenes de cocina y barra de la orden actual de la venta.
     */
    public function imprimirOrden($folio, VentaService $ventas)
    {
        $venta = Venta::find($folio);
        if ($venta == null || !$venta->venta_agente) {
            return response()->json([
                'success' => false,
                'error' => 'La venta no existe o no fue creada por el agente.',
            ], 404);
        }
        if (!$venta->estaAbierta()) {
            return response()->json([
                'success' => false,
                'error' => 'La venta ya no está abierta.',
            ], 409);
        }

        if (!$ventas->impresionActiva()) {
            return response()->json([
                'success' => false,
                'error' => 'La impresión está deshabilitada en la configuración (printStatus).',
            ], 503);
        }

        list($arrayBebidas, $arrayComidas) = $ventas->getProductosOrden($venta);
        if ($arrayBebidas->isEmpty() && $arrayComidas->isEmpty()) {
            return response()->json([
                'success' => false,
                'error' => 'La orden ' . $venta->order . ' de la venta no tiene productos.',
            ], 422);
        }

        // No se manda a cocina una orden de alitas cuyos sabores no suman las piezas vendidas;
        // la bandeja la marca con "Revisar sabores" para que el mesero la corrija.
        $problemas = [];
        foreach ($arrayComidas as $vp) {
            if ($vp->problema_sabores !== null) {
                $problemas[] = [
                    'ventasProductosId' => $vp->ventasProductosId,
                    'producto' => $vp->product->name . ' - ' . $vp->product->detail,
                    'problema' => $vp->problema_sabores,
                ];
            }
        }
        if (!empty($problemas)) {
            return response()->json([
                'success' => false,
                'error' => 'Hay órdenes con sabores que no cuadran; no se imprimió nada.',
                'problemas' => $problemas,
            ], 422);
        }

        $resultado = $ventas->printProducts($venta, $arrayBebidas, $arrayComidas);
        $ok = !in_array(false, $resultado, true);

        return response()->json([
            'success' => $ok,
            'folio' => $venta->ventaId,
            'orden' => $venta->order,
            // null = sin productos para esa impresora, true = impreso, false = falló
            'cocina' => $resultado['cocina'],
            'barra' => $resultado['barra'],
        ], $ok ? 200 : 502);
    }
}
