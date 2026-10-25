<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnNombreCuentaToVentasTable extends Migration
{
    /**
     * Nombre libre para identificar la cuenta (ej. "Cuenta de Patricia"),
     * útil cuando varias personas de una mesa pagan por separado.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('nombre_cuenta', 60)->nullable()->after('dinerstable_id');
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
            $table->dropColumn('nombre_cuenta');
        });
    }
}
