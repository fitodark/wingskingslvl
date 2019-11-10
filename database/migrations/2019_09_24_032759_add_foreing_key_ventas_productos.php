<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeingKeyVentasProductos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventasProductos', function (Blueprint $table) {
            //
            $table->foreign('IdProducto')->references('id')->on('products');
            $table->foreign('IdVenta')->references('ventaId')->on('ventas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventasProductos', function (Blueprint $table) {
            //
            $table->dropForeign('IdProducto');
            $table->dropColumn('IdVenta');
        });
    }
}
