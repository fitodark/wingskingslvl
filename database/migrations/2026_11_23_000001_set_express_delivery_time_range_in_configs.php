<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class SetExpressDeliveryTimeRangeInConfigs extends Migration
{
    /**
     * El envío express tarda entre 40 y 50 minutos en cualquier colonia (solo cambia la tarifa).
     * configs.express_delivery_time pasa de un número ("30") a un rango "MIN-MAX". Lo leen el
     * agente de WhatsApp (whatsapp-agentkit, obtener_tiempo_express) y el paso 1 del POS
     * (App\Services\TiempoEntregaService); ambos aceptan también un solo número.
     * Solo se cambia si sigue con el valor inicial "30" o si falta la clave; otro valor se respeta.
     *
     * @return void
     */
    public function up()
    {
        $fila = DB::table('configs')->where('key', 'express_delivery_time')->first();
        if ($fila === null) {
            DB::table('configs')->insert([
                'key' => 'express_delivery_time',
                'value' => '40-50',
                'order' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        }
        if (trim((string) $fila->value) === '30') {
            DB::table('configs')->where('id', $fila->id)->update(['value' => '40-50', 'updated_at' => now()]);
        }
    }

    /**
     * Regresa al número inicial (las versiones anteriores del agente solo leen un número).
     *
     * @return void
     */
    public function down()
    {
        DB::table('configs')->where('key', 'express_delivery_time')->where('value', '40-50')
            ->update(['value' => '30', 'updated_at' => now()]);
    }
}
