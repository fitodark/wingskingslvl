<?php

namespace App\Http\Controllers\Puntoventa;

use App\Client;
use App\Venta;
use App\VentasProductos;
use App\Exceptions\VentaException;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

use App\Traits\ComandasDataLibrary;

class NavVentasController extends Controller
{

    use ComandasDataLibrary;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function addMoreProducts(Request $request, VentaService $ventas, Venta $venta, Client $client = null) {
        try {
            $ventas->nuevaOrden($venta);
        } catch (VentaException $e) {
            return redirect()->route('comandas')->withErrors(['venta' => $e->getMessage()]);
        }

        return redirect()->route('drinksTab', [$venta, $client]);
    }

    /**
     * Show drinks view
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function drinksTab(Request $request, Venta $venta, Client $client = null) {
        $arrayBebidas = $this->getDrinkData($venta->ventaId);
        if ($client == null) {
            $client = Client::find($venta->client_id);
        }
        return view('puntoventa.comandas.drinksTab', compact('venta', 'arrayBebidas', 'client'));
    }

    /**
     * Show food view
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function foodsTab(Request $request, Venta $venta, Client $client = null) {
        $arrayComidas = $this->getFoodData($venta->ventaId);
        if ($client == null) {
            $client = Client::find($venta->client_id);
        }
        return view('puntoventa.comandas.foodsTab', compact('venta', 'arrayComidas', 'client'));
    }

    /**
     * Show resume view
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function resumeTab(Venta $venta, $clientId = null, $enableFooter = TRUE) {
        if ($clientId == null) {
            Log::info('client: '.$venta);
            //$client = Client::find($venta->client_id);
            $clientId = $venta->client_id;
        }
        $arrayBebidas = $this->getDrinkDataResume($venta->ventaId);
        $arrayComidas = $this->getFoodDataResume($venta->ventaId);
        $arrayDiscountPercentage = $this->getDiscountPercentage($clientId);
        $client = Client::find($clientId);
        $discountPercentage = 0;
        $montoDescuento = 0;
        $objDiscountPercentage = null;
        $objTotalSales = null;
        if (is_array($arrayDiscountPercentage) && count($arrayDiscountPercentage) > 0) {
            $objDiscountPercentage = $arrayDiscountPercentage[0];
            $discountPercentage = $arrayDiscountPercentage[0]->discountPercentage;
            $montoDescuento = $venta->montoTotal - round(($venta->montoTotal * ($discountPercentage / 100)), 0, PHP_ROUND_HALF_EVEN);
        } else {
            $totalSales = $this->getTotalVentaByClientId($clientId, date('Y-m'));
            if (is_array($totalSales) && count($totalSales) > 0) {
                $objTotalSales = $totalSales[0];
            }
        }
        return view('puntoventa.comandas.resume', compact('venta', 'arrayBebidas', 'arrayComidas', 'enableFooter'
        , 'client'
        , 'discountPercentage'
        , 'montoDescuento'
        , 'objDiscountPercentage'
        , 'objTotalSales'));
    }

    /**
     * Detalle desde la bandeja: el mismo resumen, en modo consulta.
     */
    public function detalle(Venta $venta) {
        return $this->resumeTab($venta, null, FALSE);
    }

    /**
     * "Regresar" en el wizard: descarta lo no comandado de la orden actual.
     */
    public function cancelarVenta(Request $request, VentaService $ventas, Venta $venta) {
        $ventas->cancelarOrdenActual($venta);
        return redirect()->route('comandas');
    }
}
