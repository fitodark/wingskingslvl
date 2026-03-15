<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUserCreateAndUserDeleteToVentasproductosTable extends Migration
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
            $table->unsignedBigInteger('id_user_create')->nullable()->references('id')->on('users')->after('delete_flag');
            $table->unsignedBigInteger('id_user_delete')->nullable()->references('id')->on('users')->after('delete_flag');
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
            $table->dropColumn('id_user_create');
            $table->dropColumn('id_user_delete');
        });
    }
}
