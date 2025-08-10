<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionsClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('IdVenta');
            $table->foreign('IdVenta')->references('ventaId')->on('ventas');
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->integer('porcentaje');
            $table->integer('cantidadVentas');
            $table->decimal('montoDescuento', 8, 2);
            $table->integer('estatus');
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
        Schema::dropIfExists('promotions_clients');
    }
}
