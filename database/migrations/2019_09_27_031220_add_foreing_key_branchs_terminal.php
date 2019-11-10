<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeingKeyBranchsTerminal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branchofficeterminals', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('branchoffice_id')->after('id');
            $table->foreign('branchoffice_id')->references('id')->on('branchoffices');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branchofficeterminals', function (Blueprint $table) {
            //
            $table->dropForeign('branchofficeterminals_branchoffice_id_foreign');
            $table->dropColumn('branchoffice_id');
        });
    }
}
