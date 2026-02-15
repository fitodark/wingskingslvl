<?php

namespace App\Http\Controllers;

use App\InventarioProductos;
use App\Product;

use Illuminate\Http\Request;

class InventarioProductosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $inventarioproductos = InventarioProductos::latest()->paginate(10);

        return view('inventarioproductos.index',compact('inventarioproductos'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function create()
    {
        $items = Product::where([
                ['type', '=', 1],['active', '=', true]
        ])->orderBy('name')->pluck('name', 'id');

        return view('inventarioproductos.create', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'idProducto' => 'required',
            'cantidadMinima' => 'required',
            'cantidadMaxima' => 'required'
        ]);

        $inventarioproducto = InventarioProductos::where([
                ['idProducto', '=', $request->get('idProducto')]]);
        if ($inventarioproducto->exists()) {
            return redirect()->route('inventarioproductos')->withErrors([
                    'productoexist' => 'El producto seleccionado ya se encuentra en el inventario.!'
                ])->withInput();
        }

        $currentDateTime = date('Y-m-d H:i:s');
        $inventarioproducto = new InventarioProductos([
            'idProducto' => $request->get('idProducto'),
            'cantidad' => 0,
            'cantidadMinima' => $request->get('cantidadMinima'),
            'cantidadMaxima' => $request->get('cantidadMaxima'),
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime
        ]);
        $inventarioproducto->save();
        
        return redirect()->route('inventarioproductos')
                        ->with('success','Registro creado correctamente.');
    }

    public function show(InventarioProductos $inventarioproductos)
    {
        return view('inventarioproductos.show', compact('inventarioproductos'));
    }

    public function edit(InventarioProductos $inventarioproductos)
    {
        return view('inventarioproductos.edit', compact('inventarioproductos'));
    }

    public function update(Request $request, InventarioProductos $inventarioproductos)
    {
        return redirect()->route('inventarioproductos.index')
                        ->with('success','Registro actualizado correctamente.!');
    }

    public function destroy(InventarioProductos $inventarioproductos)
    {
        $inventarioproductos->delete();

        return redirect()->route('inventarioproductos.index')
                        ->with('success','Registro eliminado correctamente.!');
    }
}
