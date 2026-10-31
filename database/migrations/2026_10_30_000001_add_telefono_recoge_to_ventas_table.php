<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTelefonoRecogeToVentasTable extends Migration
{
    /**
     * Teléfono de la persona que pasa a recoger (Para llevar), junto con nombre_cuenta.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('ventas', 'telefono_recoge')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->string('telefono_recoge', 20)->nullable()->after('nombre_cuenta');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('ventas', 'telefono_recoge')) {
            Schema::table('ventas', function (Blueprint $table) {
                $table->dropColumn('telefono_recoge');
            });
        }
    }
}
