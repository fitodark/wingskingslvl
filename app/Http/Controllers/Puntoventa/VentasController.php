<?php

namespace App\Http\Controllers\Puntoventa;

use App\Client;
use App\Venta;
use App\Dinerstable;
use App\Exceptions\VentaException;
use App\Services\VentaService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Traits\PrintSales;
use App\Traits\ComandasDataLibrary;
use App\Traits\CorteCajaDataLibrary;

class VentasController extends Controller
{

    use PrintSales;
    use ComandasDataLibrary;
    use CorteCajaDataLibrary;

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
        // validar si existe el corte de caja activo
        $corteCajaCurrent = $this->getCurrentCorteCaja(null);
        if ($corteCajaCurrent == null || $corteCajaCurrent->isEmpty()) {
            // El mesero no tiene acceso a cortes de caja: se le avisa en Home.
            if (!auth()->user()->hasAnyRole(['admin', 'encargado'])) {
                return redirect()->route('home')
                    ->withErrors(['cortecajarequired' => 'No hay corte de caja abierto. Pide al encargado que lo abra para poder generar comandas.']);
            }
            return redirect('cortecaja')
                        ->withErrors([
                            'cortecajarequired' => 'Para generar comandas, debe dar de alta el nuevo corte de caja.!',
                        ])
                        ->withInput();
        }
        return view('puntoventa.comandas.index');
    }

    /**
     * Show the form for creating a new resource.
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function create(Venta $venta = null, Client $client = null)
    {
        $discountPercentage = 0;
        $objTotalSales = null;
        if ($venta != null) {
            if ($venta->dinerstable) {
                $table = $venta->dinerstable;
            } else {
                $table = new Dinerstable();
            }

            $action = 'modify';
            $type = $venta->type;
        }
        if ($client != null){
            $client = Client::find($client->id);
        } else if ($venta->client != null){
            $client = Client::find($venta->client->id);
        }
        if ($client) {
            $arrayDiscountPercentage = $this->getDiscountPercentage($client->id);
            if (is_array($arrayDiscountPercentage) && count($arrayDiscountPercentage) > 0) {
                $discountPercentage = $arrayDiscountPercentage[0]->discountPercentage;
            } else {
                $totalSales = $this->getTotalVentaByClientId($client->id, date('Y-m'));
                if (is_array($totalSales) && count($totalSales) > 0) {
                    $objTotalSales = $totalSales[0];
                }
            }
        }

        // Sin tipo preseleccionado: una venta recién creada guarda type = Local por defecto,
        // pero hasta que se guarda el paso 1 (o se le asigna cliente) no se muestra elegido.
        $tipoElegido = null;
        if ($venta->activo || $venta->client_id || $venta->dinerstable_id || $venta->nombre_cuenta !== null) {
            $tipoElegido = (int) $venta->type;
        }

        // Cuentas abiertas por mesa (otras ventas ya comandadas), para separar cuentas en la misma mesa.
        $cuentasPorMesa = Venta::where('estatus', Venta::ESTATUS_ABIERTA)
            ->where('activo', 1)
            ->where('type', Venta::TIPO_LOCAL)
            ->whereNotNull('dinerstable_id')
            ->where('ventaId', '!=', $venta->ventaId)
            ->get(['ventaId', 'dinerstable_id', 'nombre_cuenta'])
            ->groupBy('dinerstable_id')
            ->map(function ($ventas) {
                return $ventas->map(function ($v) {
                    return $v->nombre_cuenta ?: 'Cuenta sin nombre (folio ' . $v->ventaId . ')';
                })->values()->all();
            })->all();

        return view('puntoventa.comandas.clientSelection',
            compact('action', 'venta', 'type', 'table', 'client', 'discountPercentage', 'tipoElegido', 'cuentasPorMesa'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $currentDateTime = date('Y-m-d H:i:s');
        $venta = new Venta([
            'IdUsuario' => auth()->user()->id,
            'montoTotal' => '0',
            'montoSubtotal' => '0',
            'montoIva' => '0',
            'cantidadRecibida' => '0',
            'cantidadProductos' => '0',
            'type' => Venta::TIPO_LOCAL,
            'estatus' => Venta::ESTATUS_ABIERTA,
            'activo' => 0,
            'order' => 1,
            'created_at' => $currentDateTime,
            'updated_at' => $currentDateTime
        ]);
        $venta->save();

        return redirect()->route('create', [$venta]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function show(Venta $venta) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function edit(Venta $venta) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Venta $venta, Client $client = null) {
        if ($venta != null) {
            $tipo = (int) $request->get('type');
            $mesa = ($tipo == Venta::TIPO_LOCAL && $request->filled('table')) ? $request->get('table') : null;
            $nombreCuenta = trim((string) $request->get('nombre_cuenta'));
            if ($tipo == Venta::TIPO_DOMICILIO || $nombreCuenta === '') {
                $nombreCuenta = null;
            }

            $validator = \Validator::make($request->all(), [
                'table' => 'nullable|exists:dinerstables,id',
                'nombre_cuenta' => 'nullable|string|max:60',
                'type' => 'required|in:' . implode(',', array_keys(Venta::TIPOS)),
                'clientId' => 'nullable|exists:clients,id',
            ], [
                'type.required' => 'Elige para dónde es la comanda: Mesa, Domicilio o Para llevar.',
                'type.in' => 'Elige para dónde es la comanda: Mesa, Domicilio o Para llevar.',
                'clientId.exists' => 'El cliente seleccionado ya no existe.',
                'nombre_cuenta.max' => 'El nombre de la cuenta no debe pasar de 60 caracteres.',
            ]);
            // En local basta con mesa o nombre de cuenta; en para llevar, cliente o nombre de cuenta.
            $validator->after(function ($validator) use ($tipo, $mesa, $nombreCuenta, $request) {
                if ($tipo == Venta::TIPO_LOCAL && $mesa === null && $nombreCuenta === null) {
                    $validator->errors()->add('ubicacion', 'Selecciona una mesa o escribe el nombre de la cuenta.');
                }
                if ($tipo == Venta::TIPO_DOMICILIO && !$request->filled('clientId')) {
                    $validator->errors()->add('ubicacion', 'Domicilio requiere seleccionar un cliente.');
                }
                if ($tipo == Venta::TIPO_PARA_LLEVAR && $nombreCuenta === null && !$request->filled('clientId')) {
                    $validator->errors()->add('ubicacion', 'Para llevar requiere un cliente o el nombre de la cuenta.');
                }
            });
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $venta->type = $tipo;
            $venta->dinerstable_id = $mesa;
            $venta->nombre_cuenta = $nombreCuenta;
            // En mesa no se pide cliente; en domicilio y para llevar es el registro (dirección) elegido.
            // ventas.direccion_envio (la que captura el agente) no se toca aquí.
            $venta->client_id = $tipo == Venta::TIPO_LOCAL ? null : ($request->get('clientId') ?: null);
            // Solo se guardan los campos de este paso; antes se hacía update($request->all())
            // y el formulario podía sobrescribir montos o estatus.
            $venta->save();
        }
        if ($client != null){
            $client = Client::find($client->id);
        } else {
            $client = Client::find($venta->client_id);
        }
        $discountPercentage = 0;
        if ($request->get('discountPercentage')) {
            $discountPercentage = $request->get('discountPercentage');
        }
        return redirect()->route('drinksTab', ['venta' => $venta, 'client' => $client, 'discountPercentage' => $discountPercentage]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Venta $venta) {
        //
    }

    /**
     * Cobrar desde la bandeja (modal, AJAX). Las reglas viven en VentaService::cobrar.
     */
    public function cerrarVenta(Request $request, VentaService $ventas) {
        $venta = Venta::find($request->get('ventaid'));
        if ($venta == null) {
            return response()->json(['errors' => ['No se encontró la venta.']]);
        }
        $validator = \Validator::make($request->all(), [
            'quantity' => 'required|numeric',
            'paymenttype' => 'required|in:' . implode(',', array_keys(Venta::PAGOS)),
        ], [
            'quantity.required' => 'Captura la cantidad recibida.',
            'quantity.numeric' => 'La cantidad recibida debe ser un número.',
            'paymenttype.required' => 'Selecciona el método de pago.',
            'paymenttype.in' => 'Selecciona el método de pago.',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        try {
            $ventas->cobrar($venta, $request->get('quantity'), $request->get('paymenttype'), auth()->id());
        } catch (VentaException $e) {
            return response()->json(['errors' => [$e->getMessage()]]);
        }

        return response()->json([
            'success'=>true,
            'url'=> route('comandas')
        ]);
    }

    public function printFinaliceSale(Venta $venta) {
        $options = array(
            "total" => true,
        );
        $this->printVenta($venta, $options);
    }

    public function printSale(Request $request, Venta $venta) {
        $options = array(
            "total" => false,
        );
        $this->printVenta($venta, $options);
        return redirect()->route('comandas');
    }

    /**
     * "Comandar" en el resumen: imprime cocina/barra y deja la venta activa en la bandeja.
     */
    public function printProductsOrder(Request $request, VentaService $ventas, Venta $venta) {
        try {
            $ventas->comandar($venta, $request->get('clientId'), $request->get('apply'));
        } catch (VentaException $e) {
            return redirect()->route('comandas')->withErrors(['venta' => $e->getMessage()]);
        }

        return redirect()->route('comandas');
    }
}
