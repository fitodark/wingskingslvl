<?php

namespace App\Http\Controllers;

use App\Compras;
use App\CorteCajaMovimientos;
use App\InventarioProductos;
use Illuminate\Http\Request;

use App\Traits\ComandasDataLibrary;
use App\Traits\CorteCajaDataLibrary;

class ComprasController extends Controller
{
    use ComandasDataLibrary;
    use CorteCajaDataLibrary;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // validar si existe el corte de caja activo
        $corteCajaCurrent = $this->getCurrentCorteCaja(null);
        if ($corteCajaCurrent == null || $corteCajaCurrent->isEmpty()) {
            return redirect('cortecaja')
                        ->withErrors([
                            'cortecajarequired' => 'Para generar comandas, debe dar de alta el nuevo corte de caja.!',
                        ])
                        ->withInput();
        }

        $compras = Compras::where('activo', true)->latest()->paginate(10);
        return view('compras.index',compact('compras'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function create(string $idCompra = null)
    {
        if ($idCompra == null) {
            $compra = new Compras([
                'proveedor' => '',
                'monto' => 0,
                'observaciones' => '',
                'activo' => true
            ]);
            $compra->save();
        } else {
            $compra = Compras::find($idCompra);
        }
        
        return view('compras.create', compact('compra'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor' => 'required',
            'monto' => 'required'
        ]);

        if ($request->get('idCompra') == null) {
            Compras::create($request->all());
        } else {
            $compra = Compras::find($idCompra);
            $compra->proveedor = $request->get('idCompra');
            $compra->observaciones = $request->get('observaciones');
            $compra->monto = $request->get('monto');
            $compra->save();
        }
        return redirect()->route('compras.index')
                        ->with('success','Compra creada correctamente.');
    }

    public function show(Compras $compras)
    {
        return view('compras.show',compact('compras'));
    }

    public function edit(Compras $compras)
    {
        return view('compras.edit',compact('compras'));
    }

    public function update(Request $request, Compras $compras)
    {
        $request->validate([
            'proveedor' => 'required',
            'monto' => 'required',
        ]);

        $compra = Compras::find($request->get('idCompra'));
        $compra->proveedor = $request->get('proveedor');
        $compra->observaciones = $request->get('observaciones');
        $compra->monto = $request->get('monto');
        $compra->status = true;
        $compra->activo = true;
        
        /**
         * sumar las cantidades al inventario
         */
        $this->getUpdateMontosInventario($compra->idCompra);
        $compra->save();

        // ---------- Guardar el registro de la venta en el corte de caja activo ----------
        $corteCajaCurrent = $this->getCurrentCorteCaja(null);
        if (!$corteCajaCurrent->isEmpty()) {
            $corteCaja = $corteCajaCurrent[0];
            $currentDateTime = date('Y-m-d H:i:s');

            $corteCajaMovimiento = new CorteCajaMovimientos([
                'idCorte' => $corteCaja->idCorte,
                'idVenta' => null,
                'idCompra' => $compra->idCompra,
                'descripcion' => null,
                'monto' => $compra->monto,
                'idTipo' => 2,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime
            ]);
            $corteCajaMovimiento->save();
        }
        
        // ---------- Guardar el registro de la venta en el corte de caja activo ----------

        return redirect()->route('compras')
                        ->with('success','Compra actualizada correctamente.!');
    }

    public function destroy(Compras $compras)
    {
        $compras->delete();

        return redirect()->route('compras')
                        ->with('success','Compra eliminada correctamente.!');
    }

    public function addProducts(string $idCompra)
    {
        $items = $this->getInventarioProductosDetails();
        return view('compras.comprasproductos.create', compact('idCompra','items'));
    }

    public function addCompraProductos(Request $request, Compras $compras)
    {
        $compras->delete();

        return redirect()->route('compras.index')
                        ->with('success','Compra eliminada correctamente.!');
    }

}
