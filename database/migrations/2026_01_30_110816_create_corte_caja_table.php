<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorteCajaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corte_caja', function (Blueprint $table) {
            $table->bigIncrements('idCorte');
            $table->unsignedBigInteger('idUsuarioApertura')->references('id')->on('users');;
            $table->unsignedBigInteger('idUsuarioCierre')->nullable()->references('id')->on('users');
            $table->date('fechaApertura');
		    $table->date('fechaCierre')->nullable();
            $table->decimal('montoApertura', 8, 2);
            $table->decimal('montoCierre', 8, 2);
            $table->boolean('corteStatus');
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
        Schema::dropIfExists('corte_caja');
    }
}
