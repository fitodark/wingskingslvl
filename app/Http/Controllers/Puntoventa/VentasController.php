<?php

namespace App\Http\Controllers\Puntoventa;

use App\Client;
use App\Venta;
use App\Dinerstable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Arr;

class VentasController extends Controller
{

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
        $ventas = Venta::latest()->where('activo', 1)->paginate(10);

        return view('puntoventa.comandas.index',compact('ventas'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, Venta $venta = null)
    {
      // dd($client);
      // dd($request->all());
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
        // else {
        //     $action = 'create';
        //     $venta = $this.store($request);
        //     $arrayClient = new Client([
        //       'name' => '',
        //       'phone' => '',
        //       'address' => '',
        //       'reference' => ''
        //     ]);
        //     $type = 1;
        //     $table = new Dinerstable();
        // }

        return view('puntoventa.comandas.tableSelection', compact('action', 'venta', 'type', 'arrayClient', 'table'));
        // return view('puntoventa.comandas.tableSelection', compact('venta', 'action'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      // dd($request->all());
        // if ($request->get('action') == 'create') {
            $venta = new Venta([
                'IdUsuario' => auth()->user()->id,
                'montoTotal' => '0',
                'montoSubtotal' => '0',
                'montoIva' => '0',
                'cantidadRecibida' => '0',
                'cantidadProductos' => '0',
                'type' => 1,
                'estatus' => 1,
                'activo' => 0
            ]);
            $venta->save();
        // } else {
        //     $venta = Venta::find($request->get('venta'));
        // }

        // if ($request->get('type') == 1) {
        //     $venta->dinerstable_id = $request->get('table');
        //     $venta->client_id = null;
        // } else {
        //     if (!$venta->client) {
        //         if (!$request->get('clientId')) {
        //             $client = new Client([
        //               'name' => $request->get('clientName'),
        //               'phone' => $request->get('clientPhone'),
        //               'address' => $request->get('clientAddress'),
        //               'reference' => $request->get('clientReference')
        //             ]);
        //             $client->save();
        //         } else {
        //             $client = Client::find($request->get('clientId'));
        //         }
        //         $venta->dinerstable_id = null;
        //         $venta->client_id = $client->id;
        //     }
        // }
        // $venta->type = $request->get('type');
        // $venta->save();

        return redirect()->route('create', [$venta]);
        // return $venta;
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
        // dd($request->all());
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
        // $request->validate([
        //     'quantity' => 'required'
        // ]);
        // if ($request->get('ventaid')) {
            $venta = Venta::find($request->get('ventaid'));
            // dd($venta);
        // }
        $validator = \Validator::make($request->all(), [
            'quantity' => ['required', function ($attribute, $value, $fail) use ($venta) {
                if ($value < $venta->montoTotal) {
                    $fail('La cantidad '.$attribute.' no es valida');
                }
            }]
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        return redirect()->route('comandas');
    }

}
