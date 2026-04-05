<?php

namespace App\Http\Controllers\Puntoventa;

use App\Venta;
use App\VentasProductos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

use App\Traits\ComandasDataLibrary;
use App\Services\PinAuthService;

class VentasProductosController extends Controller
{

    use ComandasDataLibrary;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $venta = Venta::find($request->get('ventaId'));
        // si al venta esta cerrada regresar al listado de comandas
        if ($venta->estatus == 2) {
            return redirect()->route('comandas');
        }
        $product = VentasProductos::where('IdProducto', $request->get('idProduct'))
            ->where('IdVenta', $request->get('ventaId'))->first();

        if (!$product or $product->product->type === 2){
            $venta->ventasProductos()->create([
                'IdProducto' => $request->get('idProduct'),
                'cantidad' => $request->get('cantidad'),
                'montoVenta' => floatval($request->get('cantidad')) * floatval($request->get('price')),
                'descripcion' => $request->get('description'),
                'estatus' => true,
                'delete_flag' => true,
                'order' => $venta->order,
                'id_user_create' => auth()->user()->id

            ]);
        } else {
            // $product->cantidad += $request->get('cantidad');
            // $product->montoVenta += $request->get('price');
            // $product->save();

            $venta->ventasProductos()->create([
                'IdProducto' => $request->get('idProduct'),
                'cantidad' => $request->get('cantidad'),
                'montoVenta' => floatval($request->get('cantidad')) * floatval($request->get('price')),
                'order' => $venta->order,
                'estatus' => true,
                'delete_flag' => true,
                'descripcion' => $request->get('description'),
                'id_user_create' => auth()->user()->id
            ]);
        }

        $venta->cantidadProductos += $request->get('cantidad');
        //$venta->montoTotal += floatval($request->get('cantidad')) * floatval($request->get('price'));
        $result = $this->getMontoTotalVenta($venta->ventaId);
        if (count($result) > 0) {
            $venta->montoTotal = $result[0]->montoVenta;
        } else {
            $venta->montoTotal = 0;
        }
        
        $venta->save();

        $ventaProductos = VentasProductos::where('IdVenta', $request->get('ventaId'))->get();

        if ($request->get('tab') == 'drinks') {
            return redirect()->route('drinksTab', [$venta, $venta->client_id]);
        } else {
            return redirect()->route('foodsTab', [$venta, $venta->client_id]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, PinAuthService $pinService)
    {
        // $request->validate([
        //     'pin' => 'required',
        //     'productoid' => 'required'
        // ]);

        $ventaProducto = VentasProductos::find($request->get('productoid'));
        $deleteFlag = $ventaProducto->delete_flag;
        // validar unicamente si el producto ya fue comandado
        // se elimina directo solo cuando la orden no ha sido finalizada
        if ($deleteFlag == 0) {
            $usuarioAutoriza = $pinService->validarPin($request->pin);
            if (!$usuarioAutoriza) {
                return response()->json(['errors' => ['pin' => 'El PIN es incorrecto!']]);
            }
        } else {
            $usuarioAutoriza = null;
        }

        $venta = Venta::find($ventaProducto->IdVenta);
        // si al venta esta cerrada regresar al listado de comandas
        if ($venta->estatus == 2) {
            return redirect()->route('comandas');
        }

        $venta->cantidadProductos -= $ventaProducto->cantidad;
        if ($usuarioAutoriza == null) {
            $ventaProducto->delete();
        } else {
            // marcar el producto como inactivo
            $ventaProducto->order = 0;
            $ventaProducto->delete_flag = false;
            $ventaProducto->estatus = 0;
            $ventaProducto->id_user_delete = $usuarioAutoriza->id;
            $ventaProducto->save();
        }

        $result = $this->getMontoTotalVenta($venta->ventaId);
        if (count($result) > 0) {
            $venta->montoTotal = $result[0]->montoVenta;
        } else {
            $venta->montoTotal = 0;
        }
        $venta->save();

        if ($request->get('tab') == 'drinks') {
            if($deleteFlag == 0) {
                return response()->json([
                    'success'=>true,
                    'url'=> route('drinksTab', [$venta, $venta->client_id])
                ]);
            } else {
                return redirect()->route('drinksTab', [$venta, $venta->client_id]);                
            }
        } else {
            if($deleteFlag == 0) {
                return response()->json([
                    'success'=>true,
                    'url'=> route('foodsTab', [$venta, $venta->client_id])
                ]);
            } else {
                return redirect()->route('foodsTab', [$venta, $venta->client_id]);                
            }
        }
    }
}
