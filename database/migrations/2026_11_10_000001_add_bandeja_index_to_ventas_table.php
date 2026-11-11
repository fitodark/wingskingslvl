<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índice para la bandeja de comandas: el refresco automático (BandejaService::firma) consulta
 * las ventas abiertas cada pocos segundos por tablet. Sin índice recorre toda la tabla.
 * Solo agrega un índice: no cambia columnas, así que no afecta los INSERT del agente.
 */
class AddBandejaIndexToVentasTable extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->index(['estatus', 'activo'], 'ventas_estatus_activo_index');
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('ventas_estatus_activo_index');
        });
    }
}
