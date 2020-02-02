<?php

namespace App\Http\Controllers\Puntoventa;

use App\Venta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Traits\ComandasDataLibrary;

class NavVentasController extends Controller
{

    use ComandasDataLibrary;

    public function __construct()
    {
        $this->middleware('auth');
    }

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
    public function drinksTab(Request $request, Venta $venta) {
        $arrayBebidas = $this->getDrinkData($venta->ventaId);
        return view('puntoventa.comandas.drinksTab', compact('venta', 'arrayBebidas'));
    }

    /**
     * Show food view
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function foodsTab(Request $request, Venta $venta) {
        $arrayComidas = $this->getFoodData($venta->ventaId);
        return view('puntoventa.comandas.foodsTab', compact('venta', 'arrayComidas'));
    }

    /**
     * Show resume view
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function resumeTab(Venta $venta, $enableFooter = TRUE) {
        $arrayBebidas = $this->getDrinkData($venta->ventaId);
        $arrayComidas = $this->getFoodData($venta->ventaId);
        return view('puntoventa.comandas.resume', compact('venta', 'arrayBebidas', 'arrayComidas', 'enableFooter'));
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
