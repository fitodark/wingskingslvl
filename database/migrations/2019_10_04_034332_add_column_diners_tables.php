<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDinersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->unsignedBigInteger('dinerstable_id')->nullable()->after('branchofficeterminal_id');
            $table->foreign('dinerstable_id')->references('id')->on('dinerstables');
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
            $table->dropForeign('ventas_dinerstable_id_foreign');
            $table->dropColumn('dinerstable_id');
        });
    }
}
