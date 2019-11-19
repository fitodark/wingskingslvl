<?php

namespace App\Http\Controllers\Puntoventa;

use App\Venta;
use App\VentasProductos;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VentasProductosController extends Controller
{
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
                'order' => $venta->order
            ]);
        }

        $venta->cantidadProductos += $request->get('cantidad');
        $venta->montoTotal += floatval($request->get('cantidad')) * floatval($request->get('price'));
        $venta->save();

        $ventaProductos = VentasProductos::where('IdVenta', $request->get('ventaId'))->get();

        if ($request->get('tab') == 'drinks') {
            return redirect()->route('drinksTab', [$venta]);
        } else {
            return redirect()->route('foodsTab', [$venta]);
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
      // return $producto;
        $venta = Venta::find($producto->IdVenta);

        $venta->cantidadProductos -= $producto->cantidad;
        $venta->montoTotal -= $producto->montoVenta;
        $venta->save();

        $producto->delete();

        if ($request->get('tab') == 'drinks') {
            return redirect()->route('drinksTab', [$venta]);
        } else {
            return redirect()->route('foodsTab', [$venta]);
        }
    }
}
