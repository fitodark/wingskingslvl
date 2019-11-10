<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeingKeyVentasBranchs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            //
            $table->dropColumn('idTerminal');
            $table->dropColumn('idSucursal');

            //$table->unsignedBigInteger('branchoffice_id')->after('IdUsuario');
            $table->unsignedBigInteger('branchofficeterminal_id')->nullable()->after('IdUsuario');

            //$table->foreign('branchoffice_id')->references('id')->on('branchoffices');
            $table->foreign('branchofficeterminal_id')->references('id')->on('branchofficeterminals');
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
            //
            //$table->dropForeign('branchoffice_id');
            $table->dropForeign('ventas_branchofficeterminal_id_foreign');

            //$table->dropColumn('branchoffice_id');
            $table->dropColumn('branchofficeterminal_id');

            $table->unsignedBigInteger('IdTerminal')->nullable()->after('IdUsuario');
            $table->unsignedBigInteger('IdSucursal')->nullable()->after('IdTerminal');
        });
    }
}
