<?php

namespace App\Http\Controllers\Puntoventa;

use App\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class SalesSummaryController extends Controller
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
    public function index($date = null)
    {
      // dd($date);
        if ($date != null) {
            $date = Carbon::createFromFormat('dmY', $date);
        } else {
            $date = now();
        }
        $total = 0;
        $ventasTotal = Venta::where('activo', 1)
            ->whereYear('created_at', '=', $date->year)
            ->whereMonth('created_at', '=', $date->month)
            ->whereDay('created_at', '=', $date->day)
            ->get();

        foreach ($ventasTotal as $value) {
            $total += $value->montoTotal;
        }
        $ventas = Venta::orderBy('ventaId', 'desc')
            ->where('activo', 1)
            ->whereYear('created_at', '=', $date->year)
            ->whereMonth('created_at', '=', $date->month)
            ->whereDay('created_at', '=', $date->day)
            ->latest()->paginate(10);
        return view('puntoventa.salessummary.index', compact('ventas', 'total'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        //
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
        //
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function findSales(Request $request)
    {
        //
        // dd($request->date);
        if ($request->has('date')) {
            $date = str_replace('/', '', $request->date);
            return redirect()->route('summary.index', [$date]);
        } else {
            return redirect()->route('summary.index');
        }
    }
}
