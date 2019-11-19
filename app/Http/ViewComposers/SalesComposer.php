<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Venta;

class SalesComposer {

    public function compose(View $view) {
        $ventas = Venta::orderBy('ventaId', 'desc')
            ->where('activo', 1)
            ->where('estatus', 1)
            ->paginate(10);
//dd($ventas);
        $view->with('ventas', $ventas)
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }
}
