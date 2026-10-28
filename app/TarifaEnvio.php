<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo de colonias/agencias para el envío a domicilio.
 * envio_gratuito = true: se ofrece envío gratuito. false: por distancia solo hay express.
 * monto_express: tarifa del servicio externo (informativa, no entra al ticket). Null = sin tarifa.
 * Lo consulta el agente de WhatsApp directo en la base; aquí solo se administra.
 */
class TarifaEnvio extends Model
{
    const TIPO_COLONIA = 'colonia';
    const TIPO_AGENCIA = 'agencia';

    const TIPOS = [
        self::TIPO_COLONIA => 'Colonia',
        self::TIPO_AGENCIA => 'Agencia',
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

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
