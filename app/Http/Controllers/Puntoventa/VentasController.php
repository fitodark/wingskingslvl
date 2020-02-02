<?php

namespace App\Http\Controllers\Puntoventa;

use App\Client;
use App\Venta;
use App\VentasProductos;
use App\Dinerstable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

use App\Traits\PrintSales;

class VentasController extends Controller
{

    use PrintSales;

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
    public function create(Request $request, Venta $venta = null)
    {
        if ($venta != null) {
            if ($venta->type == 1) {
                if ($venta->dinerstable) {
                    $table = $venta->dinerstable;
                } else {
                    $table = new Dinerstable();
                }
                $arrayClient = new Client([
                  'name' => '',
                  'phone' => '',
                  'address' => '',
                  'reference' => ''
                ]);
            } else {
                if ($venta->client) {
                    $arrayClient = $venta->client;
                } else {
                    $arrayClient = new Client([
                      'name' => '',
                      'phone' => '',
                      'address' => '',
                      'reference' => ''
                    ]);
                }
                $arrayClient = $venta->client;
                $table = new Dinerstable();
            }
            $action = 'modify';
            $type = $venta->type;
        }

        return view('puntoventa.comandas.tableSelection', compact('action', 'venta', 'type', 'arrayClient', 'table'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
            'order' => 1
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
    public function show(Venta $venta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function edit(Venta $venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Venta $venta)
    {
        if ($venta != null) {
            $venta->type = $request->get('type');
            if ($request->get('type') == 1) {
                $venta->dinerstable_id = $request->get('table');
                $venta->client_id = null;
            } else {
                $venta->dinerstable_id = null;
                $venta->client_id = $request->get('clientId');
            }
            $venta->save();
        }
        return redirect()->route('drinksTab', [$venta]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Venta $venta)
    {
        //
    }

    public function cerrarVenta(Request $request) {
        $venta = Venta::find($request->get('ventaid'));
        $validator = \Validator::make($request->all(), [
            'quantity' => ['required', function ($attribute, $value, $fail) use ($venta) {
                if ($value < $venta->montoTotal) {
                    $fail('La cantidad ingresada no es valida');
                }
            }]
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $venta->estatus = 2;
        $venta->cantidadRecibida = $request->get('quantity');
        $venta->save();
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

        $this->printProducts($venta, $arrayBebidas, $arrayComidas);
        return redirect()->route('finalizarVenta', [$venta]);
    }
}
