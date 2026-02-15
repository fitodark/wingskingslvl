<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorteCajaMovimientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corte_caja_movimientos', function (Blueprint $table) {
            $table->bigIncrements('idCorteMovimiento');
            $table->unsignedBigInteger('idCorte')->references('idCorte')->on('cortecaja');
            $table->unsignedBigInteger('idVenta')->nullable()->references('ventaId')->on('ventas');
            $table->unsignedBigInteger('idCompra')->nullable()->references('idCompra')->on('compras');
		    $table->text('descripcion')->nullable();
		    $table->decimal('monto', 8, 2);
		    $table->integer('idTipo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('corte_caja_movimientos');
    }
}
