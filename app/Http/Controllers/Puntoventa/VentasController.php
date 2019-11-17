<?php

namespace App\Http\Controllers\Puntoventa;

use App\Client;
use App\Venta;
use App\VentasProductos;
use App\Dinerstable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;
//use Mike42\Escpos\PrintConnectors\FilePrintConnector;
//use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class VentasController extends Controller
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
    public function index()
    {
        $ventas = Venta::orderBy('ventaId', 'desc')
            ->where('activo', 1)
            ->paginate(10);

        return view('puntoventa.comandas.index',compact('ventas'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     * @param  \App\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, Venta $venta = null)
    {
      // dd($client);
      // dd($request->all());
        if ($venta != null) {
            if ($venta->type == 1) {
                if ($venta->dinerstable) {
                    $table = $venta->dinerstable;
                } else {
                    $table = new Dinerstable();
                }
                $arrayClient = new Client([
                  'name' => '',
                  'phone' => '',
                  'address' => '',
                  'reference' => ''
                ]);
            } else {
                if ($venta->client) {
                    $arrayClient = $venta->client;
                } else {
                    $arrayClient = new Client([
                      'name' => '',
                      'phone' => '',
                      'address' => '',
                      'reference' => ''
                    ]);
                }
                $arrayClient = $venta->client;
                $table = new Dinerstable();
            }
            $action = 'modify';
            $type = $venta->type;
        }
        // else {
        //     $action = 'create';
        //     $venta = $this.store($request);
        //     $arrayClient = new Client([
        //       'name' => '',
        //       'phone' => '',
        //       'address' => '',
        //       'reference' => ''
        //     ]);
        //     $type = 1;
        //     $table = new Dinerstable();
        // }

        return view('puntoventa.comandas.tableSelection', compact('action', 'venta', 'type', 'arrayClient', 'table'));
        // return view('puntoventa.comandas.tableSelection', compact('venta', 'action'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $venta = new Venta([
            'IdUsuario' => auth()->user()->id,
            'montoTotal' => '0',
            'montoSubtotal' => '0',
            'montoIva' => '0',
            'cantidadRecibida' => '0',
            'cantidadProductos' => '0',
            'type' => 1,
            'estatus' => 1,
            'activo' => 0,
            'order' => 1
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
        if ($venta != null) {
            $venta->type = $request->get('type');
            if ($request->get('type') == 1) {
                $venta->dinerstable_id = $request->get('table');
                $venta->client_id = null;
            } else {
                $venta->dinerstable_id = null;
                $venta->client_id = $request->get('clientId');
            }
            $venta->save();
        }
        return redirect()->route('drinksTab', [$venta]);
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

    public function cerrarVenta(Request $request) {
      //dd($request->all());
        $venta = Venta::find($request->get('ventaid'));
        $validator = \Validator::make($request->all(), [
            'quantity' => ['required', function ($attribute, $value, $fail) use ($venta) {
                if ($value < $venta->montoTotal) {
                    $fail('La cantidad ingresada no es valida');
                }
            }]
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $venta->estatus = 2;
        $venta->cantidadRecibida = $request->get('quantity');
        $venta->save();
        $this->printFinalizarVenta($venta);

        //return redirect()->route('comandas');
        //return redirect()->action('Puntoventa\VentasController@index');
        return response()->json([
            'success'=>true,
            'url'=> route('comandas')
        ]);
    }

    public function printFinalizarVenta(Venta $venta) {
        try {
            //$connector = new WindowsPrintConnector("\\wind7\usb\epson");
            $connector = new WindowsPrintConnector("POS-80C2");
            $printer = new Printer($connector);

            # Vamos a alinear al centro lo próximo que imprimamos
            $printer->setJustification(Printer::JUSTIFY_CENTER);

            /* Intentaremos cargar e imprimir el logo */
            try{
                $logo = EscposImage::load(public_path() ."\wingsgrey.jpg", false);
                $printer->bitImage($logo);
            }catch(Exception $e){/*No hacemos nada si hay error*/}

            /* Ahora vamos a imprimir un encabezado */
            $printer->text("Wings Kings" . "\n");
            $printer->text("Bravo #30 Col. Centro, Huajuapan de León" . "\n");
            $printer->text("Oaxaca, CP 69005, Pedidos al: 953 117 5127" . "\n");
            $printer->text("Fecha: " . date("Y-m-d H:i:s") . "\n\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Folio #: " . $venta->ventaId . "\n");

            if ($venta->type == 1) {
                $printer->text("Ubicación: " . $venta->dinerstable->name . "\n\n");
            } else if ($venta->type == 2) {
                $printer->text("Cliente: " . $venta->client->name . ", (" . $venta->client->phone . ")" . "\n");
                $printer->text("         " . $venta->client->address . " - " . $venta->client->reference. "\n\n");
            }

            # Para mostrar el total
            foreach ($venta->ventasProductos as $ventaProducto) {

                /*Alinear a la izquierda para la cantidad y el nombre*/
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($ventaProducto->cantidad . " x " . $ventaProducto->product->name . ' - '
                    . $ventaProducto->product->detail . "\n");

                /*Y a la derecha para el importe*/
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text('$ ' . $ventaProducto->montoVenta . "\n");
            }

            /* Terminamos de imprimir los productos, ahora va el total */
            $printer->text("--------\n");
            $printer->text("TOTAL: $". round($venta->montoTotal, 2) ."\n");
            $printer->text("RECIBI: $". round($venta->cantidadRecibida, 2) ."\n");
            $printer->text("CAMBIO: $". round(floatval($venta->cantidadRecibida) - floatval($venta->montoTotal), 2)."\n");

            /* Podemos poner también un pie de página */
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("GRACIAS POR SU PROPINA\n");
            $printer->text("***** Muchas gracias por su compra *****\n");

            /*Alimentamos el papel 3 veces*/
            $printer->feed(1);

            $printer->cut();
            $printer->close();
        } catch(Exception $e) {
            echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
        }

    }

    public function print(Request $request, Venta $venta) {

        try {
            //$connector = new WindowsPrintConnector("\\wind7\usb\epson");
            $connector = new WindowsPrintConnector("POS-80C2");
            $printer = new Printer($connector);

            # Vamos a alinear al centro lo próximo que imprimamos
            $printer->setJustification(Printer::JUSTIFY_CENTER);

            /* Intentaremos cargar e imprimir el logo */
            try{
                $logo = EscposImage::load(public_path() ."\wingsgrey.jpg", false);
                $printer->bitImage($logo);
            }catch(Exception $e){/*No hacemos nada si hay error*/}

            /* Ahora vamos a imprimir un encabezado */
            $printer->text("Wings Kings" . "\n");
            $printer->text("Bravo #30 Col. Centro, Huajuapan de León" . "\n");
            $printer->text("Oaxaca, CP 69005, Pedidos al: 953 117 5127" . "\n");
            $printer->text("Fecha: " . date("Y-m-d H:i:s") . "\n\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Folio #: " . $venta->ventaId . "\n");

            if ($venta->type == 1) {
                $printer->text("Ubicación: " . $venta->dinerstable->name . "\n\n");
            } else if ($venta->type == 2) {
                $printer->text("Cliente: " . $venta->client->name . ", (" . $venta->client->phone . ")" . "\n");
                $printer->text("         " . $venta->client->address . " - " . $venta->client->reference. "\n\n");
            }

            # Para mostrar el total
            foreach ($venta->ventasProductos as $ventaProducto) {

                /*Alinear a la izquierda para la cantidad y el nombre*/
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text($ventaProducto->cantidad . " x " . $ventaProducto->product->name . ' - '
                    . $ventaProducto->product->detail . "\n");

                /*Y a la derecha para el importe*/
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                $printer->text('$ ' . $ventaProducto->montoVenta . "\n");
            }

            /* Terminamos de imprimir los productos, ahora va el total */
            $printer->text("--------\n");
            $printer->text("TOTAL: $". $venta->montoTotal ."\n");

            /* Podemos poner también un pie de página */
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("GRACIAS POR SU PROPINA\n");
            $printer->text("***** Muchas gracias por su compra *****\n");

            /*Alimentamos el papel 3 veces*/
            $printer->feed(1);

            $printer->cut();
            $printer->close();
            return redirect()->route('comandas');
        } catch(Exception $e) {
            echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
        }
    }

    public function printProductsOrder (Request $request, Venta $venta) {

        $arrayBebidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->where('order', $venta->order)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', '1');
            })->get();

        $arrayComidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->where('order', $venta->order)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', 2)->orWhere('type', '=', 3);
            })->get();
//dd($arrayComidas->isEmpty());
//return $arrayComidas;
        // IMPRIMIR ORDE DE COMIDA
        if (!$arrayComidas->isEmpty()) {
            try {
                $connector = new WindowsPrintConnector("POS-80C2");
                $printer = new Printer($connector);

                # Vamos a alinear al centro lo próximo que imprimamos
                $printer->setJustification(Printer::JUSTIFY_CENTER);

                /* Ahora vamos a imprimir un encabezado */
                $printer->text("Wings Kings" . "\n");
                $printer->text("Orden de Cocina" . "\n");
                #La fecha también
                $printer->text(date("Y-m-d H:i:s") . "\n");

                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Folio: " . $venta->ventaId . "\n");

                if ($venta->type == 1) {
                    $printer->text("Ubicación: " . $venta->dinerstable->name . "\n\n");
                } else if ($venta->type == 2) {
                    $printer->text("Cliente: " . $venta->client->name . ", (" . $venta->client->phone . ")" . "\n");
                    $printer->text("         " . $venta->client->address . " - " . $venta->client->reference. "\n\n");
                }

                # Para mostrar el total
                foreach ($arrayComidas as $ventaProducto) {

                    /*Alinear a la izquierda para la cantidad y el nombre*/
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text($ventaProducto->cantidad . " x " . $ventaProducto->product->name
                        . ' - '  . $ventaProducto->product->detail . "\n");

                    if ($ventaProducto->product->type == 2 ) {
                        $printer->setJustification(Printer::JUSTIFY_RIGHT);
                        foreach (json_decode($ventaProducto->descripcion, TRUE) as $key => $value) {
                            $printer->text($value[0]['value'] . ' - ' . $value[1]['value']. "\n");
                        }
                    }
                }

                /* Podemos poner también un pie de página */
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("-------------------\n");
                $printer->text("Fin Orden de Cocina\n");

                /*Alimentamos el papel 3 veces*/
                $printer->feed(1);

                $printer->cut();
                $printer->close();

                //return redirect()->route('comandas');
                //return redirect()->route('finalizarVenta', [$venta]);
            } catch(Exception $e) {
                echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
            }
        }

        // IMPRIMIR ORDE DE BEBIDAS
        if (!$arrayBebidas->isEmpty()) {
            try {
                $connector = new WindowsPrintConnector("POS-80C2");
                $printer = new Printer($connector);

                # Vamos a alinear al centro lo próximo que imprimamos
                $printer->setJustification(Printer::JUSTIFY_CENTER);

                /* Ahora vamos a imprimir un encabezado */
                $printer->text("Wings Kings" . "\n");
                $printer->text("Orden de Barra" . "\n");
                #La fecha también
                $printer->text(date("Y-m-d H:i:s") . "\n");

                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("Folio: " . $venta->ventaId . "\n");

                if ($venta->type == 1) {
                    $printer->text("Ubicación: " . $venta->dinerstable->name . "\n\n");
                } else if ($venta->type == 2) {
                    $printer->text("Cliente: " . $venta->client->name . ", (" . $venta->client->phone . ")" . "\n");
                    $printer->text("         " . $venta->client->address . " - " . $venta->client->reference. "\n\n");
                }

                # Para mostrar el total
                foreach ($arrayBebidas as $ventaProducto) {

                    /*Alinear a la izquierda para la cantidad y el nombre*/
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text($ventaProducto->cantidad . " x " . $ventaProducto->product->name
                        . ' - '  . $ventaProducto->product->detail . "\n");
                }

                /* Podemos poner también un pie de página */
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text("-------------------\n");
                $printer->text("Fin Orden de Barra\n");

                /*Alimentamos el papel 3 veces*/
                $printer->feed(1);

                $printer->cut();
                $printer->close();

            } catch(Exception $e) {
                echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
            }
        }

        return redirect()->route('finalizarVenta', [$venta]);
    }
}
