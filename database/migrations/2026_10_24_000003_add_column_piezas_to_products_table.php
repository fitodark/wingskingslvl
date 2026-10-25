<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPiezasToProductsTable extends Migration
{
    /**
     * Piezas que trae una orden (alitas/boneless). Con esto se valida que los
     * sabores capturados sumen lo vendido. Null = el producto no se valida.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedSmallInteger('piezas')->nullable()->after('detail');
        });

        // Se toma el número del nombre o detalle de los productos de Cocina (Alitas),
        // solo cuando va seguido de una unidad: "10 Piezas", "15pz", "8 Boneless", "30 Alitas".
        $productos = DB::table('products')->where('type', 2)->get(['id', 'name', 'detail']);
        foreach ($productos as $p) {
            $texto = $p->name . ' ' . $p->detail;
            if (preg_match('/(\d+)\s*(pz|piezas|alitas|alas|boneless|boneles|bonless)\b/i', $texto, $m)) {
                DB::table('products')->where('id', $p->id)->update(['piezas' => (int) $m[1]]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('piezas');
        });
    }
}
