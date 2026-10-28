<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnvioToVentasTable extends Migration
{
    /**
     * Tipo de envío de los pedidos a domicilio del agente de WhatsApp.
     * Todas nullable: las ventas del POS y los pedidos para recoger las dejan en NULL.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            // 1 = envío gratuito, 2 = envío express. NULL = no es a domicilio o venta del POS.
            $table->unsignedTinyInteger('tipo_envio')->nullable()->after('direccion_envio');
            // Monto express aceptado por el cliente. Informativo: NO entra a montoTotal ni al ticket.
            $table->decimal('monto_envio_express', 8, 2)->nullable()->after('tipo_envio');
            // Colonia contra la que se consultó la tarifa (texto, por si la tarifa cambia después).
            $table->string('colonia_envio', 150)->nullable()->after('monto_envio_express');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['tipo_envio', 'monto_envio_express', 'colonia_envio']);
        });
    }
}
