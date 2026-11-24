<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo de colonias/agencias para el envío a domicilio.
 * envio_gratuito = true: se ofrece envío gratuito. false: por distancia solo hay express.
 * monto_express: tarifa del servicio externo (informativa, no entra al ticket). Null = sin tarifa.
 * Lo consulta el agente de WhatsApp directo en la base; aquí solo se administra.
 *
 * Cobertura (se deriva de las dos columnas, sin columna propia, para no tocar al agente):
 * - gratuito: envio_gratuito = 1 y monto_express NULL. El agente solo ofrece gratuito; en el
 *   paso 1 el mesero puede dar express con un monto acordado por teléfono.
 * - ambos:    envio_gratuito = 1 y monto_express con tarifa. El cliente elige; el express cobra la tarifa.
 * - express:  envio_gratuito = 0 y monto_express con tarifa (obligatoria). Solo express.
 */
class TarifaEnvio extends Model
{
    const TIPO_COLONIA = 'colonia';
    const TIPO_AGENCIA = 'agencia';

    const TIPOS = [
        self::TIPO_COLONIA => 'Colonia',
        self::TIPO_AGENCIA => 'Agencia',
    ];

    const COBERTURA_GRATUITO = 'gratuito';
    const COBERTURA_AMBOS = 'ambos';
    const COBERTURA_EXPRESS = 'express';

    const COBERTURAS = [
        self::COBERTURA_GRATUITO => 'Solo envío gratuito',
        self::COBERTURA_AMBOS => 'Envío gratuito y express',
        self::COBERTURA_EXPRESS => 'Solo envío express (por distancia)',
    ];

    protected $table = 'tarifas_envio';

    protected $fillable = [
        'colonia', 'tipo', 'envio_gratuito', 'monto_express', 'activo',
    ];

    protected $casts = [
        'envio_gratuito' => 'boolean',
        'activo' => 'boolean',
        'monto_express' => 'float',
    ];

    /**
     * Cobertura según envio_gratuito y monto_express (ver arriba).
     */
    public function getCoberturaAttribute()
    {
        if (!$this->envio_gratuito) {
            return self::COBERTURA_EXPRESS;
        }
        return $this->monto_express !== null ? self::COBERTURA_AMBOS : self::COBERTURA_GRATUITO;
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
