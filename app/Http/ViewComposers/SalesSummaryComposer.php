<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Venta;

class SalesSummaryComposer {

    public function compose(View $view) {
        $total = 0;
        $ventasTotal = Venta::where('activo', 1)
            ->whereYear('created_at', '=', now()->year)
            ->whereMonth('created_at', '=', now()->month)
            ->whereDay('created_at', '=', now()->day)
            ->get();

        foreach ($ventasTotal as $value) {
            $total += $value->montoTotal;
        }

        $ventas = Venta::orderBy('ventaId', 'desc')
            ->where('activo', 1)
            ->whereYear('created_at', '=', now()->year)
            ->whereMonth('created_at', '=', now()->month)
            ->whereDay('created_at', '=', now()->day)
            ->paginate(10);

        $view->with('ventas', $ventas)
            ->with('total', $total)
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }
}
