<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\InventarioProductos;

class InventarioProductosComposer
{

    public function compose(View $view)
    {
        $inventarioproductos = InventarioProductos::orderBy('idInventarioProducto', 'asc')->paginate(20);

        $view->with('inventarioproductos', $inventarioproductos)
            ->with('i', (request()->input('page', 1) - 1) * 20);
    }
}
