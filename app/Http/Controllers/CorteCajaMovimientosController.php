<?php

namespace App\Http\Controllers;

use App\CorteCajaMovimientos;

use Illuminate\Http\Request;
use App\Traits\CorteCajaDataLibrary;

class CorteCajaMovimientosController extends Controller
{
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
            //return view('cortecaja.index');
            return redirect('cortecaja')
                ->withErrors([
                    'cortecajarequired' => 'Para generar movimientos, debe dar de alta el nuevo corte de caja.!',
                ])
                ->withInput();
        }
        $cortecajamovimientos = CorteCajaMovimientos::latest()->paginate(10);

        return view('cortecajamovimientos.index',compact('cortecajamovimientos'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function create()
    {
        return view('cortecajamovimientos.create');
    }

    public function store(Request $request)
    {
        // validar si ya existe el corte de caja
        $corteCajaCurrent = $this->getCurrentCorteCaja(null);
        $corteCaja = $corteCajaCurrent[0];

        $request->validate([
            'descripcion' => 'required',
            'monto' => 'required',
        ]);

        $currentDateTime = date('Y-m-d H:i:s');
        CorteCajaMovimientos::create([
            'idCorte' => $corteCaja->idCorte,
            'idVenta' => null,
            'idCompra' => null,
            'descripcion' => $request->get('descripcion'),
            'monto' => $request->get('monto'),
            'idTipo' => 3,
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime
        ]);

        return redirect()->route('cortecajamovimientos')
                        ->with('success','Movimiento del Corte de Caja creado correctamente.');
    }

    public function show(CorteCajaMovimientos $cortecajamovimientos)
    {
        return view('cortecajamovimientos.show', compact('cortecajamovimientos'));
    }

    public function edit(CorteCajaMovimientos $cortecajamovimientos)
    {
        return view('cortecajamovimientos.edit', compact('cortecajamovimientos'));
    }

    public function update(Request $request, CorteCajaMovimientos $cortecajamovimientos)
    {
        $request->validate([
            'idUsuarioApertura' => 'required',
            'fechaApertura' => 'required',
            'montoApertura' => 'required'
        ]);

        $cortecaja->update($request->all());

        return redirect()->route('cortecajamovimientos.index')
                        ->with('success','Corte de Caja actualizado correctamente.!');
    }

    public function destroy(CorteCajaMovimientos $cortecajamovimientos)
    {
        $cortecaja->delete();

        return redirect()->route('cortecajamovimientos.index')
                        ->with('success','Corte de Caja eliminado correctamente.!');
    }
}
