<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEstatusToVentasproductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventasproductos', function (Blueprint $table) {
            //
            $table->boolean('estatus')->nullable()->after('order')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventasproductos', function (Blueprint $table) {
            //
            $table->dropColumn('estatus');
        });
    }
}
