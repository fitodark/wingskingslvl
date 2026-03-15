<?php

namespace App\Http\Controllers;

use App\CorteCaja;
use App\CorteCajaMovimientos;
use Illuminate\Http\Request;
use App\Traits\CorteCajaDataLibrary;
use App\Traits\ComandasDataLibrary;

class CorteCajaController extends Controller
{
    use CorteCajaDataLibrary;
    use ComandasDataLibrary;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $request->user()->authorizeRoles(['admin', 'encargado']);

        $cortecaja = CorteCaja::latest()->paginate(10);

        return view('cortecaja.index',compact('cortecaja'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function create()
    {
        return view('cortecaja.create');
    }

    public function store(Request $request)
    {
       // validar si ya existe el corte de caja
        $corteCaja = $this->getCurrentCorteCaja($request->get('fechaapertura'));
        $corteCajaCurrent = $this->getCurrentCorteCaja(null);
        $currentDate = date('Y-m-d');

        $validator = \Validator::make($request->all(), [
            'fechaapertura' => ['required', function ($attribute, $value, $fail) use ($corteCaja, $corteCajaCurrent, $currentDate) {
                if ($corteCajaCurrent != null && !$corteCajaCurrent->isEmpty() 
                    && $corteCajaCurrent[0]->fechaApertura == $value) {
                    $fail('El corte de caja para la fecha: '.$value.', se encuentra activo.!' );
                } else if ($value > $currentDate) {
                    $fail('El corte de caja para la fecha: '.$value.', es mayor a la fecha actual.!' );
                } 
                // else if ($corteCaja != null && !$corteCaja->isEmpty()) {
                //     $fail('El corte de caja para la fecha: '.$value.', ya fue cerrado anteriormente.!' );
                // }
            }],
            'montoapertura' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect('cortecaja/create')
                        ->withErrors($validator)
                        ->withInput();
        }

        $currentDateTime = date('Y-m-d H:i:s');
        $cortecaja = new CorteCaja([
            'idUsuarioApertura' => auth()->user()->id,
            'fechaApertura' => $request->get('fechaapertura'),
            'montoApertura' => $request->get('montoapertura'),
            'montoCierre' => 0,
            'corteStatus' => 1,
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime
        ]);
        $cortecaja->save();

        return redirect()->route('cortecaja')
                        ->with('success','Corte de Caja creado correctamente.');
    }

    public function show(CorteCaja $cortecaja)
    {
        return view('cortecaja.show', compact('cortecaja'));
    }

    public function edit(CorteCaja $cortecaja)
    {
        return view('cortecaja.edit', compact('cortecaja'));
    }

    public function update(Request $request, CorteCaja $cortecaja)
    {
        $request->validate([
            'idUsuarioApertura' => 'required',
            'fechaApertura' => 'required',
            'montoApertura' => 'required'
        ]);

        $cortecaja->update($request->all());

        return redirect()->route('cortecaja.index')
                        ->with('success','Corte de Caja actualizado correctamente.!');
    }

    public function destroy(CorteCaja $cortecaja)
    {
        $cortecaja->delete();

        return redirect()->route('cortecaja.index')
                        ->with('success','Corte de Caja eliminado correctamente.!');
    }

    public function current(string $date = null)
    {
        return $this->getCurrentCorteCaja($date);
    }

    public function cerrarcortecaja(Request $request)
    {
        $idCorte = $request->get('idcorte');
        if ($idCorte == null) {
            return redirect()->route('cortecaja')->withErrors([
                    'cortecajarequired' => 'No se pudo cerrar el Corte de Caja seleccionado.!',
                ]);
        }

        $cortecaja = CorteCaja::find($idCorte);

        $currentDateTime = date('Y-m-d H:i:s');
        $currentDate = date('Y-m-d');

        $cortecaja->idUsuarioCierre = auth()->user()->id;
        $cortecaja->fechaCierre = $currentDate;
        $cortecaja->corteStatus = false;
        $cortecaja->updated_at = $currentDateTime;
        $cortecaja->save();

        return response()->json([
            'success'=>true,
            'url'=> route('cortecaja')
        ]);
    }

    public function detalles(Request $request, string $idCorte = null)
    {
        $request->user()->authorizeRoles(['admin']);

        if ($idCorte == null) {
            return redirect()->route('cortecaja');
        }
        $cortecaja = CorteCaja::find($idCorte);

        $getCorteCajaCurrent = $this->getCurrentCorteCaja(null);
        if ($getCorteCajaCurrent == null || $getCorteCajaCurrent->isEmpty()) {
            $corteCajaCurrent = $cortecaja;
        } else {
            $corteCajaCurrent = $getCorteCajaCurrent[0];
        }

        //$cortecajamovimientos = $cortecaja->corteCajaMovimientos->orderBy('idCorteMovimiento', 'desc');
        $cortecajamovimientos = CorteCajaMovimientos::where('idCorte', $idCorte)
            ->orderBy('idCorteMovimiento', 'desc')->get();
        $totalVentasEfectivo = $this->getTotalVenta($idCorte, 1);
        if ($totalVentasEfectivo == null || empty($totalVentasEfectivo)) {
            $totalVentasEfectivoArray = (object) [
                'montoTotalVentas' => 0
            ];
        } else {
            $totalVentasEfectivoArray = $totalVentasEfectivo[0];
        }

        $totalVentasTransfer = $this->getTotalVenta($idCorte, 2);
        if ($totalVentasTransfer == null || empty($totalVentasTransfer)) {
            $totalVentasTransferArray = (object) [
                'montoTotalVentas' => 0
            ];
        } else {
            $totalVentasTransferArray = $totalVentasTransfer[0];
        }

        $totalCompras = $this->getTotalCompras($idCorte);
        if ($totalCompras == null || empty($totalCompras) || $totalCompras->isEmpty()) {
            $totalComprasArray = (object) [
                'montoTotalCompras' => 0
            ];
        } else {
            $totalComprasArray = $totalCompras[0];
        }

        return view('cortecaja.resume', compact('cortecajamovimientos',
            'corteCajaCurrent',
            'totalVentasEfectivoArray',
            'totalVentasTransferArray',
            'totalComprasArray'));
    }
}
