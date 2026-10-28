<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationToClientsTable extends Migration
{
    /**
     * Última ubicación (pin) que el cliente compartió por WhatsApp. Referencia para el
     * repartidor; nunca sustituye la dirección escrita.
     * El agente ya creó las columnas en producción, por eso se revisa antes de crearlas.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'location_lat')) {
                $table->decimal('location_lat', 10, 7)->nullable()->after('reference');
            }
            if (!Schema::hasColumn('clients', 'location_lng')) {
                $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            }
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
            $table->dropColumn(['location_lat', 'location_lng']);
        });
    }
}
