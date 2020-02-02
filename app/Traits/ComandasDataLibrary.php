<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\VentasProductos;

trait ComandasDataLibrary {

    public function getDrinkData($ventaId) {
        $arrayBebidas = VentasProductos::where('IdVenta', $ventaId)
              ->whereHas('product', function (Builder $query) {
                  $query->where('type', '=', '1');
              })->get();
        return $arrayBebidas;
    }

    public function getFoodData($ventaId) {
        $arrayComidas = VentasProductos::where('IdVenta', $ventaId)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', 2)->orWhere('type', '=', 3);
            })->get();
        return $arrayComidas;
    }

}
