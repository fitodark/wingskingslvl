<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddExpressDeliveryTimeToConfigs extends Migration
{
    /**
     * Tiempo estimado del envío express en minutos (lo lee el agente).
     * Va en migración para no correr ConfigSeeder completo en producción: ese seeder
     * busca por llave + valor y duplicaría las claves que ya se cambiaron en la base.
     * Si la clave ya existe no se toca (pudieron ajustar el valor).
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('configs')->where('key', 'express_delivery_time')->exists()) {
            return;
        }
        DB::table('configs')->insert([
            'key' => 'express_delivery_time',
            'value' => '30',
            'order' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('configs')->where('key', 'express_delivery_time')->delete();
    }
}
