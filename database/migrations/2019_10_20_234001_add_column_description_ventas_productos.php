<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDescriptionVentasProductos extends Migration
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
            $table->string('descripcion')->nullable()->after('montoVenta');
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
            $table->dropColumn('descripcion');
        });
    }
}
