<?php

namespace Tests\Feature;

use App\Client;
use App\Traits\PrintSales;
use App\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\Printer;
use Tests\TestCase;

/**
 * Leyenda "SERVICIO EXPRESS" en los tickets de cocina y barra (plan-envio-express.md §7.1).
 * Se imprime contra un conector falso y se revisan los bytes ESC/POS generados.
 */
class TicketExpressTest extends TestCase
{
    use RefreshDatabase;

    // ESC/POS: ESC a n (justificación) y ESC E n (negritas)
    const CENTRO = "\x1ba\x01";
    const IZQUIERDA = "\x1ba\x00";
    const NEGRITAS_ON = "\x1bE\x01";
    const NEGRITAS_OFF = "\x1bE\x00";

    private function ticketCocina(Venta $venta)
    {
        $impresora = new class {
            use PrintSales;
        };
        $connector = new DummyPrintConnector();
        $printer = new Printer($connector);
        $impresora->getPrinterBody($printer, $venta, collect(), [
            'title' => 'Wings King', 'header' => 'Orden de Cocina', 'fooder' => 'Fin Orden de Cocina',
        ]);
        $bytes = $connector->getData();
        $printer->close();
        return $bytes;
    }

    private function ventaDomicilio(array $datos = [])
    {
        $cliente = Client::create([
            'name' => 'Juan Pérez', 'phone' => '5500000303', 'address' => 'Calle 3',
            'reference' => 'Zaguán negro', 'colonia' => 'Acatlima', 'active' => 1,
        ]);
        return $this->ventaAbierta($this->usuario('mesero'), array_merge([
            'type' => Venta::TIPO_DOMICILIO, 'clientId' => $cliente->id,
        ], $datos));
    }

    public function test_venta_express_imprime_leyenda_centrada_en_negritas_despues_de_la_direccion()
    {
        $venta = $this->ventaDomicilio([
            'tipo_envio' => Venta::ENVIO_EXPRESS, 'monto_envio_express' => 45, 'colonia_envio' => 'Acatlima',
        ]);

        $bytes = $this->ticketCocina($venta);

        $leyenda = self::CENTRO . self::NEGRITAS_ON . "SERVICIO EXPRESS\n" . self::NEGRITAS_OFF . self::IZQUIERDA;
        $this->assertStringContainsString($leyenda, $bytes);
        $this->assertGreaterThan(strpos($bytes, 'Calle 3'), strpos($bytes, 'SERVICIO EXPRESS'));
        $this->assertStringNotContainsString('$', $bytes); // sin monto express
    }

    public function test_venta_gratuita_o_sin_tipo_de_envio_no_imprime_leyenda()
    {
        $gratuita = $this->ventaDomicilio(['tipo_envio' => Venta::ENVIO_GRATUITO, 'colonia_envio' => 'Acatlima']);
        $this->assertStringNotContainsString('SERVICIO EXPRESS', $this->ticketCocina($gratuita));

        $sinTipo = $this->ventaDomicilio(['tipo_envio' => null]);
        $this->assertStringNotContainsString('SERVICIO EXPRESS', $this->ticketCocina($sinTipo));
    }
}
