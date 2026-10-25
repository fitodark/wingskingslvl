<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultPaymentTypeOnVentasTable extends Migration
{
    /**
     * Al crear una venta todavía no se sabe si se pagará en efectivo (1)
     * o transferencia (2); el método se asigna al cerrar la venta.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->integer('payment_type')->nullable()->default(null)->change();
        });

        // Las ventas abiertas aún no se han cobrado.
        DB::table('ventas')->where('estatus', 1)->update(['payment_type' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('ventas')->whereNull('payment_type')->update(['payment_type' => 1]);

        Schema::table('ventas', function (Blueprint $table) {
            $table->integer('payment_type')->nullable(false)->default(1)->change();
        });
    }
}
