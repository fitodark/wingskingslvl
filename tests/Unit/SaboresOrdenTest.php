<?php

namespace Tests\Unit;

use App\Support\SaboresOrden;
use App\Venta;
use PHPUnit\Framework\TestCase;

/**
 * Reglas puras (sin base de datos) de sabores y etiquetas de venta.
 */
class SaboresOrdenTest extends TestCase
{
    private function json(array $renglones)
    {
        $datos = [[['key' => '', 'value' => 'Cantidad'], ['key' => '', 'value' => 'Sabor']]];
        foreach ($renglones as list($c, $s)) {
            $datos[] = [['key' => '0', 'value' => $c], ['key' => '0', 'value' => $s]];
        }
        return json_encode($datos);
    }

    public function test_renglones_omite_el_encabezado()
    {
        $r = SaboresOrden::renglones($this->json([['10 Piezas', 'Habanero']]));
        $this->assertSame([['cantidad' => '10 Piezas', 'sabor' => 'Habanero']], $r);
    }

    public function test_renglones_regresa_null_si_el_json_esta_mal()
    {
        $this->assertNull(SaboresOrden::renglones('no es json'));
        $this->assertNull(SaboresOrden::renglones('[[{"value":"Cantidad"}],[{"value":"10 Piezas"}]]'));
        $this->assertNull(SaboresOrden::renglones(null));
    }

    public function test_piezas_de_cada_cantidad()
    {
        $this->assertSame(10.0, SaboresOrden::piezasDeCantidad('10 Piezas', 20));
        $this->assertSame(20.0, SaboresOrden::piezasDeCantidad('Todas', 20));
        $this->assertEquals(10, SaboresOrden::piezasDeCantidad('Mitad', 20));
        $this->assertNull(SaboresOrden::piezasDeCantidad('Un montón', 20));
    }

    public function test_problema_detecta_sabores_que_no_suman()
    {
        $this->assertNull(SaboresOrden::problema($this->json([['Mitad', 'Habanero'], ['Mitad', 'Bufalo']]), 10));
        $this->assertSame('Sabores suman 15 de 10 piezas',
            SaboresOrden::problema($this->json([['10 Piezas', 'Habanero'], ['5 Piezas', 'Bufalo']]), 10));
        $this->assertSame('Sin sabores', SaboresOrden::problema('basura', 10));
        // Sin piezas configuradas en el producto no se valida la suma.
        $this->assertNull(SaboresOrden::problema($this->json([['5 Piezas', 'Habanero']]), null));
    }

    public function test_etiquetas_de_venta()
    {
        $this->assertSame('Para llevar', Venta::TIPOS[Venta::TIPO_PARA_LLEVAR]);
        $this->assertSame('Efectivo', Venta::PAGOS[Venta::PAGO_EFECTIVO]);

        $venta = new Venta(['type' => Venta::TIPO_PARA_LLEVAR, 'nombre_cuenta' => 'Cuenta de Patricia']);
        $this->assertTrue($venta->esParaLlevar());
        $this->assertSame('Para Llevar (Pasan a recoger) - Cuenta de Patricia', $venta->ubicacion);

        $venta = new Venta(['apply_discount' => 1, 'montoTotal' => 200, 'montoTotalDescuento' => 180]);
        $this->assertEquals(180, $venta->monto_a_cobrar);
    }
}
