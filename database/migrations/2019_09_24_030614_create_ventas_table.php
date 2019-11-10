<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->bigIncrements('ventaId');
            $table->unsignedBigInteger('IdProducto');
            $table->unsignedBigInteger('IdUsuario');
            $table->unsignedBigInteger('IdTerminal');
            $table->unsignedBigInteger('IdSucursal');
            $table->decimal('montoTotal', 8, 2);
            $table->decimal('montoSubtotal', 8, 2);
            $table->decimal('montoIva', 8, 2);
            $table->decimal('cantidadRecibida', 8, 2);
            $table->integer('cantidadProductos');
            $table->integer('estatus');
            $table->boolean('activo');
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
        Schema::dropIfExists('ventas');
    }
}
