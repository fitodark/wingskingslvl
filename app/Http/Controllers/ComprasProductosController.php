<?php

namespace App\Http\Controllers;

use App\ComprasProductos;
use Illuminate\Http\Request;

class ComprasProductosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $comprasproductos = ComprasProductos::latest()->paginate(10);

        return view('compras.index',compact('comprasproductos'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function create()
    {
        return view('compras.comprasproductos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'idCompra' => 'required',
            'idProducto' => 'required',
            'cantidad' => 'required',
            'monto' => 'required'
        ]);

        $currentDateTime = date('Y-m-d H:i:s');
        $comprasproductos = new ComprasProductos([
            'idCompra' => $request->get('idCompra'),
            'idProducto' => $request->get('idProducto'),
            'cantidad' => $request->get('cantidad'),
            'monto' => $request->get('monto'),
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime
        ]);
        $comprasproductos->save();

        return redirect()->route('compras.create', [$request->get('idCompra')])
                        ->with('success','Registro creado correctamente.');
    }

    public function show(ComprasProductos $comprasproductos)
    {
        return view('compras.show', compact('comprasproductos'));
    }

    public function edit(ComprasProductos $comprasproductos)
    {
        return view('compras.edit', compact('comprasproductos'));
    }

    public function update(Request $request, ComprasProductos $comprasproductos)
    {
        return redirect()->route('compras.index')
                        ->with('success','Registro actualizado correctamente.!');
    }

    public function destroy(ComprasProductos $comprasproductos)
    {
        $comprasproductos->delete();

        return redirect()->route('compras.index')
                        ->with('success','Registro eliminado correctamente.!');
    }

    public function delete(string $idCompraProducto = null)
    {
        if ($idCompraProducto != null) {
            $comprasproductos = ComprasProductos::find($idCompraProducto);
            $idCompra = $comprasproductos->idCompra;
            $comprasproductos->delete();

            return redirect()->route('compras.create', [$idCompra])
                ->with('success','Registro eliminado correctamente.');
        }
    }
}
