<?php

namespace App\Http\Controllers\Puntoventa;

use App\Client;
use App\Venta;
use App\VentasProductos;
use App\PromotionsClients;
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

    public function addMoreProducts(Request $request, Venta $venta, Client $client = null) {
        $venta->order += 1;
        $venta->save();

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
        $arrayBebidas = $this->getDrinkData($venta->ventaId);
        $arrayComidas = $this->getFoodData($venta->ventaId);
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

    public function finalizarVenta(Request $request, Venta $venta) {
        $clientId = $request->get('clientId');
        $apply = $request->get('apply');
        $ventaDB = Venta::find($venta->ventaId);

        Log::info('client: '.$clientId);
        $arrayDiscountPercentage = $this->getDiscountPercentage($clientId);
        Log::info('apply: '.$apply);
        if (is_array($arrayDiscountPercentage) && count($arrayDiscountPercentage) > 0 && $apply == 1) {
            $currentDateTime = date('Y-m-d H:i:s');

            $objDiscountPercentage = $arrayDiscountPercentage[0];
            $discountPercentage = $objDiscountPercentage->discountPercentage;
            $montoDescuento = $ventaDB->montoTotal - round(($ventaDB->montoTotal * ($discountPercentage / 100)), 0, PHP_ROUND_HALF_EVEN);

            $ventaDB->montoTotalDescuento = $montoDescuento;
            $ventaDB->apply_discount = TRUE;
        } else {
            $ventaDB->apply_discount = FALSE;
        }
        //User::where('active', 0)->update(['status' => 'inactive']);
        VentasProductos::where('IdVenta', $venta->ventaId)
            ->where('delete_flag', true)
            ->where([
                ['estatus', '=', 1]
            ])
            ->update(['delete_flag' => false]);
        $ventaDB->activo = 1;
        $ventaDB->save();
        return redirect()->route('comandas', [$venta]);
    }

    public function cancelarVenta(Request $request, Venta $venta = null) {
        VentasProductos::where('IdVenta', $venta->ventaId)
            ->where('delete_flag', true)
            ->where([
                ['estatus', '=', 1]
            ])->update(['estatus' => 0]);

        $result = $this->getMontoTotalVenta($venta->ventaId);
        if (count($result) > 0) {
            $venta->montoTotal = $result[0]->montoVenta;
        } else {
            $venta->montoTotal = 0;
        }
        $venta->save();
        return redirect()->route('comandas');
    }
}
