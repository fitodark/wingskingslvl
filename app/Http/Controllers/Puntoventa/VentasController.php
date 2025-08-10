<?php

namespace App\Http\Controllers\Puntoventa;

use App\Client;
use App\Venta;
use App\VentasProductos;
use App\Dinerstable;
use App\Config;
use App\PromotionsClients;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

use App\Traits\PrintSales;
use App\Traits\ComandasDataLibrary;

class VentasController extends Controller
{

    use PrintSales;
    use ComandasDataLibrary;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('puntoventa.comandas.index');
    }

    /**
     * Show the form for creating a new resource.
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function create(Venta $venta = null, Client $client = null)
    {
        $discountPercentage = 0;
        $objTotalSales = null;
        if ($venta != null) {
            if ($venta->dinerstable) {
                $table = $venta->dinerstable;
            } else {
                $table = new Dinerstable();
            }

            $action = 'modify';
            $type = $venta->type;
        }
        if ($client != null){
            $client = Client::find($client->id);
        } else if ($venta->client != null){
            $client = Client::find($venta->client->id);
        }
        if ($client) {
            $arrayDiscountPercentage = $this->getDiscountPercentage($client->id);
            if (is_array($arrayDiscountPercentage) && count($arrayDiscountPercentage) > 0) {
                $discountPercentage = $arrayDiscountPercentage[0]->discountPercentage;
            } else {
                $totalSales = $this->getTotalVentaByClientId($client->id, date('Y-m'));
                if (is_array($totalSales) && count($totalSales) > 0) {
                    $objTotalSales = $totalSales[0];
                }
            }
        }

        return view('puntoventa.comandas.clientSelection', 
            compact('action', 'venta', 'type', 'table', 'client', 'discountPercentage'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $currentDateTime = date('Y-m-d H:i:s');
        $venta = new Venta([
            'IdUsuario' => auth()->user()->id,
            'montoTotal' => '0',
            'montoSubtotal' => '0',
            'montoIva' => '0',
            'cantidadRecibida' => '0',
            'cantidadProductos' => '0',
            'type' => 1,
            'estatus' => 1,
            'activo' => 0,
            'order' => 1,
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime
        ]);
        $venta->save();

        return redirect()->route('create', [$venta]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function show(Venta $venta) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function edit(Venta $venta) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Venta $venta, Client $client = null) {
        if ($venta != null) {
            $venta->type = $request->get('type');
            if ($request->get('type') == 1) {
                $venta->dinerstable_id = $request->get('table');
            } else {
                $venta->dinerstable_id = null;
            }
            $venta->client_id = $request->get('clientId');
            $venta->update($request->all());
        }
        if ($client != null){
            $client = Client::find($client->id);
        } else {
            $client = Client::find($venta->client_id);
        }
        $discountPercentage = 0;
        if ($request->get('discountPercentage')) {
            $discountPercentage = $request->get('discountPercentage');
        }
        return redirect()->route('drinksTab', ['venta' => $venta, 'client' => $client, 'discountPercentage' => $discountPercentage]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Venta $venta) {
        //
    }

    public function cerrarVenta(Request $request) {
        $venta = Venta::find($request->get('ventaid'));
        $validator = \Validator::make($request->all(), [
            'quantity' => ['required', function ($attribute, $value, $fail) use ($venta) {
                if ($venta->apply_discount == 0 && $value < $venta->montoTotal) {
                    $fail('La cantidad ingresada es menor al Monto Total');
                } else if ($venta->apply_discount == 1 && $value < $venta->montoTotalDescuento) {
                    $fail('La cantidad ingresada es menor al Monto Total con descuento');
                }
            }]
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $venta->estatus = 2;
        $venta->cantidadRecibida = $request->get('quantity');
        $venta->save();

        // Guardar registro de promocion
        $date = date("Y-m-d");
        Log::info('date: '.$date);

        $promotionDB = $this->getPromotionsClients($venta->ventaId, $date);
        if ($venta->apply_discount == 1 && $promotionDB->isEmpty()) {
            $arrayDiscountPercentage = $this->getDiscountPercentage($venta->client_id);
            $objDiscountPercentage = $arrayDiscountPercentage[0];

            Log::info('create promotion');
            $promotion = new PromotionsClients ([
                'IdVenta' => $venta->ventaId,
                'client_id' => $venta->client_id,
                'porcentaje' => $objDiscountPercentage->discountPercentage,
                'cantidadVentas' => $objDiscountPercentage->total,
                'montoDescuento'=> $venta->montoTotalDescuento,
                'estatus' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $promotion->save();
        }
        // -------------------------------

        $this->printFinaliceSale($venta);

        return response()->json([
            'success'=>true,
            'url'=> route('comandas')
        ]);
    }

    public function printFinaliceSale(Venta $venta) {
        $options = array(
            "total" => true,
        );
        $this->printVenta($venta, $options);
    }

    public function printSale(Request $request, Venta $venta) {
        $options = array(
            "total" => false,
        );
        $this->printVenta($venta, $options);
        return redirect()->route('comandas');
    }

    public function printProductsOrder(Request $request, Venta $venta) {
        $printStatus = Config::where('key', '=', 'printStatus')->first();

        $arrayBebidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->where('order', $venta->order)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', '1');
            })->get();

        $arrayComidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->where('order', $venta->order)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', 2)->orWhere('type', '=', 3);
            })->get();

        if ($printStatus->value == 'true') {
            try{
                $this->printProducts($venta, $arrayBebidas, $arrayComidas);
            } catch (Exception | ErrorException $e) {
                Log::info('Exception to print: '.$e->getMessage());
            }
        }
        $clientId = $request->get('clientId');
        $apply = $request->get('apply');
        return redirect()->route('finalizarVenta', ['venta' => $venta, 'clientId' => $clientId, 'apply' => $apply]);
    }
}
