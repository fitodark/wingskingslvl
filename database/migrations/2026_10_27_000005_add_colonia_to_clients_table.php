<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColoniaToClientsTable extends Migration
{
    /**
     * Colonia de la dirección del cliente. Texto libre (no FK a tarifas_envio) para no
     * romper clientes existentes. El agente la llena al dar de alta; en el POS se elige
     * de la lista de colonias del catálogo.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('colonia', 150)->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('colonia');
        });
    }
}
