<?php
namespace App\Traits;

use App\Venta;
use App\Config;

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
            }catch(Exception $e){/*No hacemos nada si hay error*/}

            /* Ahora vamos a imprimir un encabezado */
            $printer->text($titleTicket->value . "\n");
            $printer->text($addressTicket->value . "\n");
            $printer->text($addressComTicket->value . "\n");
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
                if ($ventaProducto->estatus == 1) {
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
        } catch(Exception $e) {
            echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
        }

    }

    public function printProducts(Venta $venta, $arrayBebidas, $arrayComidas) {
        $printKitchen = Config::where('key', '=', 'printKitchen')->first();
        $printBar = Config::where('key', '=', 'printBar')->first();
        $titleTicket = Config::where('key', '=', 'titleTicket')->first();
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
            } catch(Exception $e) {
                echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
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
            } catch(Exception $e) {
                echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
            }
        }
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

        if ($venta->type == 1) {
            $printer->text("Ubicación: " . $venta->dinerstable->name . "\n\n");
        } else if ($venta->type == 2 or $venta->type == 3) {
            $printer->text("Cliente: " . $venta->client->name . ", (" . $venta->client->phone . ")" . "\n");
            $printer->text("         " . $venta->client->address . "\n\n");
        }

        foreach ($arrayProductos as $ventaProducto) {
            if ($ventaProducto->estatus == 1) {
            /*Alinear a la izquierda para la cantidad y el nombre*/
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($ventaProducto->cantidad . " x " . $ventaProducto->product->name
                .(is_null($ventaProducto->product->detail)?  "\n":' - '  . $ventaProducto->product->detail . "\n")
                .(($ventaProducto->product->type == 2)? '':
                    (is_null($ventaProducto->descripcion)? '':"["  . $ventaProducto->descripcion . "]" . "\n")
                ));

            if ($ventaProducto->product->type == 2 ) {
                $printer->setJustification(Printer::JUSTIFY_RIGHT);
                foreach (json_decode($ventaProducto->descripcion, TRUE) as $key => $value) {
                    $printer->text($value[0]['value'] . ' - ' . $value[1]['value']. "\n");
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
