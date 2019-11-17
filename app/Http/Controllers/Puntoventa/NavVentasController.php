<?php

namespace App\Http\Controllers\Puntoventa;

use App\Client;
use App\Venta;
use App\VentasProductos;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Builder;

class NavVentasController extends Controller
{

    public function addMoreProducts(Request $request, Venta $venta) {
        $venta->order += 1;
        $venta->save();

        return redirect()->route('drinksTab', [$venta]);
    }

    /**
     * Show drinks view
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function drinksTab(Request $request, Venta $venta)
    {
//      dd($request->all());
        $arrayBebidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', '1');
            })->get();
        return view('puntoventa.comandas.drinksTab', compact('venta', 'arrayBebidas'));
    }

    /**
     * Show food view
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function foodsTab(Request $request, Venta $venta)
    {
        // dd($request->all());
        $arrayComidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', 2)->orWhere('type', '=', 3);
            })->get();
        return view('puntoventa.comandas.foodsTab', compact('venta', 'arrayComidas'));
    }

    /**
     * Show resume view
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function resumeTab(Venta $venta)
    {
        $arrayBebidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', '1');
            })->get();

        $arrayComidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', 2)->orWhere('type', '=', 3);
            })->get();

        return view('puntoventa.comandas.resume', compact('venta', 'arrayBebidas', 'arrayComidas'));
    }

    public function finalizarVenta(Request $request, Venta $venta) {
        $venta->activo = 1;
        $venta->save();

        return redirect()->route('comandas');
    }

    public function cancelarVenta(Request $request, Venta $venta = null) {
        return redirect()->route('comandas');
    }
}
