<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContadorIndecisionTable extends Migration
{
    /**
     * Intercambios seguidos en los que el cliente esquiva la pregunta de cierre.
     * Lo usa el agente de WhatsApp (marcar_indecision); a 4 deja la plática en stand-by.
     * El agente ya la creó en producción, por eso no falla si existe.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('contador_indecision')) {
            return;
        }
        Schema::create('contador_indecision', function (Blueprint $table) {
            $table->string('telefono', 50)->primary(); // chat_id de WhatsApp
            $table->unsignedInteger('contador')->default(0);
            $table->timestamp('actualizado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contador_indecision');
    }
}
