<?php

namespace App\Http\Controllers\Puntoventa;

use App\Venta;
use App\VentasProductos;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Traits\ComandasDataLibrary;

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
        $product = VentasProductos::where('IdProducto', $request->get('idProduct'))
            ->where('IdVenta', $request->get('ventaId'))->first();

        if (!$product or $product->product->type === 2){
            $venta->ventasProductos()->create([
                'IdProducto' => $request->get('idProduct'),
                'cantidad' => $request->get('cantidad'),
                'montoVenta' => floatval($request->get('cantidad')) * floatval($request->get('price')),
                'descripcion' => $request->get('description'),
                'order' => $venta->order
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
                'descripcion' => $request->get('description'),
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
    public function destroy(Request $request, VentasProductos $producto)
    {
        $producto->delete();
        $venta = Venta::find($producto->IdVenta);

        $venta->cantidadProductos -= $producto->cantidad;
        $result = $this->getMontoTotalVenta($venta->ventaId);
        if (count($result) > 0) {
            $venta->montoTotal = $result[0]->montoVenta;
        } else {
            $venta->montoTotal = 0;
        }
        $venta->save();

        if ($request->get('tab') == 'drinks') {
            return redirect()->route('drinksTab', [$venta, $venta->client_id]);
        } else {
            return redirect()->route('foodsTab', [$venta, $venta->client_id]);
        }
    }
}
