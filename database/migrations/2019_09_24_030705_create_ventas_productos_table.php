<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVentasProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventasProductos', function (Blueprint $table) {
            $table->bigIncrements('ventasProductosId');
            $table->unsignedBigInteger('IdProducto');
            $table->unsignedBigInteger('IdVenta');
            $table->integer('cantidad');
            $table->decimal('montoVenta', 8, 2);
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
        Schema::dropIfExists('ventasProductos');
    }
}
