<?php
namespace App\Traits;

use App\Venta;
use App\Config;
use Illuminate\Support\Facades\Log;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;

trait PrintSales {

    public function printVenta(Venta $venta, $options = null) {
        $printStatus = Config::where('key', '=', 'printStatus')->first();
        $printPrincipal = Config::where('key', '=', 'printPrincipal')->first();
        $logoTicket = Config::where('key', '=', 'logoTicket')->first();
        $titleTicket = Config::where('key', '=', 'titleTicket')->first();
        $addressTicket = Config::where('key', '=', 'addressTicket')->first();
        $addressComTicket = Config::where('key', '=', 'addressComTicket')->first();
        $fooderPropTicket = Config::where('key', '=', 'fooderPropTicket')->first();
        $fooderTicket = Config::where('key', '=', 'fooderTicket')->first();
        $discountPercentage = Config::where('key', '=', 'discountPercentage')->first();

        try {
            if ($printStatus->value == 'true') {
            $connector = new WindowsPrintConnector($printPrincipal->value);
            $printer = new Printer($connector);

            # Vamos a alinear al centro lo próximo que imprimamos
            $printer->setJustification(Printer::JUSTIFY_CENTER);

            /* Intentaremos cargar e imprimir el logo */
            try{
                $logo = EscposImage::load(public_path() ."\\".$logoTicket->value, false);
                $printer->bitImage($logo);
            }catch(\Exception $e){/*No hacemos nada si hay error*/}

            /* Ahora vamos a imprimir un encabezado */
            $printer->text($titleTicket->value . "\n");
            $printer->text($addressTicket->value . "\n");
            $printer->text($addressComTicket->value . "\n");
            $printer->text("Fecha: " . date("Y-m-d H:i:s") . "\n\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Folio #: " . $venta->ventaId . "\n");

            if ($venta->esLocal()) {
                $printer->text("Ubicación: " . $venta->ubicacion . "\n\n");
            } else if ($venta->esDomicilio()) {
                $printer->text("Cliente: " . $venta->client->name . ", (" . $venta->client->phone . ")" . "\n");
                $printer->text("         " . ($venta->direccion_envio ?: $venta->client->address) . " - " . $venta->client->reference. "\n\n");
            } else if ($venta->esParaLlevar() && (!empty($venta->nombre_cuenta) || !empty($venta->telefono_recoge))) {
                if (!empty($venta->nombre_cuenta)) {
                    $printer->text("Cuenta: " . $venta->nombre_cuenta . "\n");
                }
                if (!empty($venta->telefono_recoge)) {
                    $printer->text("Tel. recoge: " . $venta->telefono_recoge . "\n");
                }
                $printer->text("\n");
            }
            # Para mostrar el total
            foreach ($venta->ventasProductos as $ventaProducto) {
                if ($ventaProducto->estaActivo()) {
                    /*Alinear a la izquierda para la cantidad y el nombre*/
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text($ventaProducto->cantidad . " x " . $ventaProducto->product->name . ' - '
                        . $ventaProducto->product->detail . "\n");

                    /*Y a la derecha para el importe*/
                    $printer->setJustification(Printer::JUSTIFY_RIGHT);
                    $printer->text('$ ' . $ventaProducto->montoVenta . "\n");
                }
            }
            /* Terminamos de imprimir los productos, ahora va el total */
            $printer->text("--------\n");
            $printer->text("TOTAL: $". round($venta->montoTotal, 2) ."\n");
            if ($venta->apply_discount == 1) {
                $printer->text("DESCUENTO: ". $discountPercentage->value ." %\n");
                $printer->text("CON DESCUENTO: $". round($venta->montoTotalDescuento, 2) ."\n");
            }
            if ($options['total']) {
                $printer->text("RECIBI: $". round($venta->cantidadRecibida, 2) ."\n");
                if ($venta->apply_discount == 1) {
                    $printer->text("CAMBIO: $". round(floatval($venta->cantidadRecibida) - floatval($venta->montoTotalDescuento), 2)."\n");
                } else {
                    $printer->text("CAMBIO: $". round(floatval($venta->cantidadRecibida) - floatval($venta->montoTotal), 2)."\n");
                }
            }

            /* Podemos poner también un pie de página */
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text($fooderPropTicket->value ."\n");
            $printer->text($fooderTicket->value. "\n");

            /*Alimentamos el papel 3 veces*/
            $printer->feed(1);

            $printer->cut();
            $printer->close();
            }
        } catch(\Exception $e) {
            Log::warning('No se pudo imprimir el ticket: ' . $e->getMessage());
        }

    }

    public function printProducts(Venta $venta, $arrayBebidas, $arrayComidas) {
        $printKitchen = Config::where('key', '=', 'printKitchen')->first();
        $printBar = Config::where('key', '=', 'printBar')->first();
        $titleTicket = Config::where('key', '=', 'titleTicket')->first();
        // null = no había productos para esa impresora, true = se imprimió, false = falló
        $resultado = ['cocina' => null, 'barra' => null];
        // IMPRIMIR ORDE DE COMIDA
        if (!$arrayComidas->isEmpty()) {
            try {
                $connector = new WindowsPrintConnector($printKitchen->value);
                $printer = new Printer($connector);
                $options = array(
                    "title" => $titleTicket->value,
                    "header" => "Orden de Cocina",
                    "fooder" => "Fin Orden de Cocina"
                );
                $printer = $this->getPrinterBody($printer, $venta, $arrayComidas, $options);

                $printer->feed(1);
                $printer->cut();
                $printer->close();
                $resultado['cocina'] = true;
            } catch(\Exception $e) {
                $resultado['cocina'] = false;
                Log::warning('No se pudo imprimir la orden de cocina: ' . $e->getMessage());
            }
        }

        // IMPRIMIR ORDE DE BEBIDAS
        if (!$arrayBebidas->isEmpty()) {
            try {
                $connector = new WindowsPrintConnector($printBar->value);
                $printer = new Printer($connector);
                $options = array(
                    "title" => $titleTicket->value,
                    "header" => "Orden de Barra",
                    "fooder" => "Fin Orden de Barra"
                );
                $printer = $this->getPrinterBody($printer, $venta, $arrayBebidas, $options);

                $printer->feed(1);
                $printer->cut();
                $printer->close();
                $resultado['barra'] = true;
            } catch(\Exception $e) {
                $resultado['barra'] = false;
                Log::warning('No se pudo imprimir la orden de barra: ' . $e->getMessage());
            }
        }
        return $resultado;
    }

    public function getPrinterBody(Printer $printer, Venta $venta, $arrayProductos, $options) {
        # Vamos a alinear al centro lo próximo que imprimamos
        $printer->setJustification(Printer::JUSTIFY_CENTER);

        /* Ahora vamos a imprimir un encabezado */
        $printer->text($options['title'] . "\n");
        $printer->text($options['header'] . "\n");
        #La fecha también
        $printer->text(date("Y-m-d H:i:s") . "\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Folio: " . $venta->ventaId . "\n");

        if ($venta->esLocal()) {
            $printer->text("Ubicación: " . $venta->ubicacion . "\n\n");
        } else {
            if ($venta->esParaLlevar() && !empty($venta->nombre_cuenta)) {
                $printer->text("Cuenta: " . $venta->nombre_cuenta . "\n");
            }
            if ($venta->esParaLlevar() && !empty($venta->telefono_recoge)) {
                $printer->text("Tel. recoge: " . $venta->telefono_recoge . "\n");
            }
            if (!empty($venta->client)) {
                $printer->text("Cliente: " . $venta->client->name . ", (" . $venta->client->phone . ")" . "\n");
                $printer->text("         " . ($venta->direccion_envio ?: $venta->client->address) . "\n");
            }
            $printer->text("\n");
        }

        foreach ($arrayProductos as $ventaProducto) {
            if ($ventaProducto->estaActivo()) {
            /*Alinear a la izquierda para la cantidad y el nombre*/
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($ventaProducto->cantidad . " x " . $ventaProducto->product->name
                .(is_null($ventaProducto->product->detail)?  "\n":' - '  . $ventaProducto->product->detail . "\n")
                .($ventaProducto->product->llevaSabores() ? '':
                    (is_null($ventaProducto->descripcion)? '':"["  . $ventaProducto->descripcion . "]" . "\n")
                ));

            if ($ventaProducto->product->llevaSabores()) {
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                // Mismo formato que antes (encabezado + renglones), pero un JSON mal formado
                // ya no tumba la orden completa: solo se omiten los sabores.
                $renglones = \App\Support\SaboresOrden::renglones($ventaProducto->descripcion);
                if ($renglones) {
                    $printer->text("Cantidad - Sabor\n");
                    foreach ($renglones as $renglon) {
                        $printer->text($renglon['cantidad'] . ' - ' . $renglon['sabor'] . "\n");
                    }
                }
            }
            }
        }
        /* Podemos poner también un pie de página */
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("-------------------\n");
        $printer->text($options['fooder']. "\n");
        return $printer;
    }

}
