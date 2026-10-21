<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNumerosBloqueadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('numeros_bloqueados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('telefono', 50)->unique();
            $table->text('motivo');
            $table->timestamp('bloqueado_en')->useCurrent();
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
        Schema::dropIfExists('numeros_bloqueados');
    }
}
