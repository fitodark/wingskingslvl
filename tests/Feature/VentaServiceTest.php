<?php

namespace Tests\Feature;

use App\CorteCajaMovimientos;
use App\Exceptions\VentaException;
use App\InventarioProductos;
use App\Services\VentaService;
use App\Venta;
use App\VentasProductos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Reglas del flujo de venta: lo que mueve dinero e inventario.
 */
class VentaServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var VentaService */
    private $ventas;
    private $mesero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ventas = app(VentaService::class);
        $this->mesero = $this->usuario('mesero');
        $this->config('printStatus', 'false');
    }

    private function esperarError(callable $accion, $campo)
    {
        try {
            $accion();
        } catch (VentaException $e) {
            $this->assertSame($campo, $e->campo, $e->getMessage());
            return $e;
        }
        $this->fail("Se esperaba VentaException ($campo)");
    }

    // ---------- Agregar ----------

    public function test_agregar_toma_el_precio_del_catalogo_y_recalcula_totales()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $agua = $this->producto(['price' => 25]);

        $vp = $this->ventas->agregarProducto($venta, $agua, 3, null, $this->mesero->id);

        $this->assertEquals(75, $vp->montoVenta);
        $this->assertTrue((bool) $vp->delete_flag, 'recién agregado = no comandado');
        $this->assertSame($this->mesero->id, (int) $vp->id_user_create);
        $venta->refresh();
        $this->assertEquals(75, $venta->montoTotal);
        $this->assertEquals(3, $venta->cantidadProductos);
    }

    public function test_agregar_rechaza_cantidades_invalidas()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $agua = $this->producto();

        foreach ([0, -2, 'abc', '1.5', null] as $cantidad) {
            $this->esperarError(function () use ($venta, $agua, $cantidad) {
                $this->ventas->agregarProducto($venta, $agua, $cantidad, null, $this->mesero->id);
            }, 'cantidad');
        }
        $this->assertSame(0, VentasProductos::count());
    }

    public function test_agregar_alitas_valida_que_los_sabores_sumen_la_orden()
    {
        $this->catalogoSabores();
        $venta = $this->ventaAbierta($this->mesero);
        $alitas = $this->alitas(10);

        $e = $this->esperarError(function () use ($venta, $alitas) {
            $this->ventas->agregarProducto($venta, $alitas, 1,
                $this->sabores([['10 Piezas', 'Habanero'], ['5 Piezas', 'Bufalo']]), $this->mesero->id);
        }, 'sabores');
        $this->assertStringContainsString('suman 15 piezas', $e->getMessage());

        $this->esperarError(function () use ($venta, $alitas) {
            $this->ventas->agregarProducto($venta, $alitas, 1,
                $this->sabores([['10 Piezas', 'Sabor inventado']]), $this->mesero->id);
        }, 'sabores');

        $vp = $this->ventas->agregarProducto($venta, $alitas, 1,
            $this->sabores([['5 Piezas', 'Habanero'], ['5 Piezas', 'Bufalo']]), $this->mesero->id);
        $this->assertNull($vp->fresh()->problema_sabores);
    }

    public function test_no_se_agrega_a_una_venta_cerrada()
    {
        $venta = $this->ventaAbierta($this->mesero, ['estatus' => Venta::ESTATUS_CERRADA]);

        $e = $this->esperarError(function () use ($venta) {
            $this->ventas->agregarProducto($venta, $this->producto(), 1, null, $this->mesero->id);
        }, VentaException::VENTA_CERRADA);
        $this->assertTrue($e->esVentaCerrada());
    }

    // ---------- Eliminar ----------

    public function test_eliminar_sin_comandar_borra_el_renglon()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $vp = $this->ventas->agregarProducto($venta, $this->producto(['price' => 25]), 2, null, $this->mesero->id);

        $this->ventas->eliminarProducto($vp->fresh());

        $this->assertNull(VentasProductos::find($vp->ventasProductosId));
        $this->assertEquals(0, $venta->fresh()->montoTotal);
        $this->assertEquals(0, $venta->fresh()->cantidadProductos);
    }

    public function test_eliminar_comandado_es_baja_logica_con_quien_autorizo()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $agua = $this->producto(['price' => 25]);
        $vp = $this->ventas->agregarProducto($venta, $agua, 2, null, $this->mesero->id);
        $this->ventas->agregarProducto($venta, $agua, 1, null, $this->mesero->id);
        $this->ventas->comandar($venta->fresh(), null, 0);
        $vp = $vp->fresh();

        $this->esperarError(function () use ($vp) {
            $this->ventas->eliminarProducto($vp, null);
        }, 'pin');

        $encargado = $this->usuario('encargado');
        $this->ventas->eliminarProducto($vp, $encargado);

        $vp = $vp->fresh();
        $this->assertNotNull($vp, 'no se borra: queda para auditoría');
        $this->assertFalse($vp->estaActivo());
        $this->assertSame($encargado->id, (int) $vp->id_user_delete);
        $this->assertEquals(25, $venta->fresh()->montoTotal);
        $this->assertEquals(1, $venta->fresh()->cantidadProductos);

        // Eliminarlo otra vez no vuelve a restar.
        $this->esperarError(function () use ($vp, $encargado) {
            $this->ventas->eliminarProducto($vp, $encargado);
        }, 'producto');
        $this->assertEquals(1, $venta->fresh()->cantidadProductos);
    }

    public function test_regresar_descarta_solo_lo_no_comandado()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $agua = $this->producto(['price' => 25]);
        $comandado = $this->ventas->agregarProducto($venta, $agua, 1, null, $this->mesero->id);
        $this->ventas->comandar($venta->fresh(), null, 0);

        $venta = $venta->fresh();
        $this->ventas->nuevaOrden($venta);
        $nuevo = $this->ventas->agregarProducto($venta, $agua, 4, null, $this->mesero->id);

        $this->ventas->cancelarOrdenActual($venta);

        $this->assertTrue($comandado->fresh()->estaActivo());
        $this->assertFalse($nuevo->fresh()->estaActivo());
        $this->assertEquals(25, $venta->fresh()->montoTotal);
        $this->assertEquals(1, $venta->fresh()->cantidadProductos);
    }

    public function test_comandar_marca_productos_y_activa_la_venta()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $vp = $this->ventas->agregarProducto($venta, $this->producto(), 1, null, $this->mesero->id);

        $this->ventas->comandar($venta->fresh(), null, 0);

        $this->assertTrue($vp->fresh()->estaComandado());
        $this->assertEquals(1, $venta->fresh()->activo);
    }

    public function test_finalizar_otra_vez_no_reimprime_la_ronda_ya_comandada()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $agua = $this->producto();
        $alitas = $this->alitas(10);
        $this->catalogoSabores();

        // Ronda 1: bebida y comida
        $this->ventas->agregarProducto($venta, $agua, 2, null, $this->mesero->id);
        $this->ventas->agregarProducto($venta->fresh(), $alitas, 1,
            $this->sabores([['Todas', 'Habanero']]), $this->mesero->id);
        list($bebidas, $comidas) = $this->ventas->productosPorComandar($venta->fresh());
        $this->assertSame([1, 1], [$bebidas->count(), $comidas->count()]);
        $this->ventas->comandar($venta->fresh(), null, 0);

        // Ronda 2: solo una bebida nueva
        $this->ventas->nuevaOrden($venta->fresh());
        $this->assertFalse($venta->fresh()->tiene_ronda_pendiente, 'ronda recién abierta, sin productos');
        $nueva = $this->ventas->agregarProducto($venta->fresh(), $agua, 1, null, $this->mesero->id);
        $this->assertTrue($venta->fresh()->tiene_ronda_pendiente);
        $this->assertTrue($nueva->fresh()->esPendienteDeRonda($venta->fresh()));
        list($bebidas, $comidas) = $this->ventas->productosPorComandar($venta->fresh());
        $this->assertSame([$nueva->ventasProductosId], $bebidas->pluck('ventasProductosId')->all());
        $this->assertTrue($comidas->isEmpty(), 'la comida de la ronda 1 no se vuelve a mandar');

        $this->ventas->comandar($venta->fresh(), null, 0);

        // Detalle -> Finalizar otra vez: ya no hay nada que imprimir ni que pintar en verde.
        list($bebidas, $comidas) = $this->ventas->productosPorComandar($venta->fresh());
        $this->assertTrue($bebidas->isEmpty() && $comidas->isEmpty());
        $this->assertFalse($venta->fresh()->tiene_ronda_pendiente);
        $this->assertFalse($nueva->fresh()->esPendienteDeRonda($venta->fresh()));
    }

    // ---------- Cobrar ----------

    public function test_cobrar_cierra_registra_en_corte_y_descuenta_inventario_una_vez()
    {
        $corte = $this->corteAbierto($this->mesero, 500);
        $agua = $this->producto(['price' => 25]);
        $inventario = $this->inventario($agua, 20);
        $venta = $this->ventaAbierta($this->mesero);
        // Dos renglones del mismo producto: antes solo se descontaba uno.
        $this->ventas->agregarProducto($venta, $agua, 2, null, $this->mesero->id);
        $this->ventas->agregarProducto($venta, $agua, 1, null, $this->mesero->id);
        $eliminado = $this->ventas->agregarProducto($venta, $agua, 5, null, $this->mesero->id);
        $this->ventas->eliminarProducto($eliminado);
        $this->ventas->comandar($venta->fresh(), null, 0);

        $cerrada = $this->ventas->cobrar($venta->fresh(), 100, Venta::PAGO_EFECTIVO, $this->mesero->id);

        $this->assertTrue($cerrada->estaCerrada());
        $this->assertEquals(Venta::PAGO_EFECTIVO, $cerrada->payment_type);
        $this->assertEquals(100, $cerrada->cantidadRecibida);
        $this->assertSame($this->mesero->id, (int) $cerrada->id_user_close);

        $movimiento = CorteCajaMovimientos::where('idVenta', $venta->ventaId)->first();
        $this->assertEquals(75, $movimiento->monto);
        $this->assertEquals(CorteCajaMovimientos::TIPO_VENTA, $movimiento->idTipo);
        $this->assertEquals(575, $corte->fresh()->montoCierre);
        $this->assertEquals(17, InventarioProductos::find($inventario->idInventarioProducto)->cantidad);

        // Doble clic / reintento: no vuelve a cerrar ni a descontar.
        $this->esperarError(function () use ($venta) {
            $this->ventas->cobrar($venta->fresh(), 100, Venta::PAGO_EFECTIVO, $this->mesero->id);
        }, VentaException::VENTA_CERRADA);
        $this->assertEquals(17, InventarioProductos::find($inventario->idInventarioProducto)->cantidad);
        $this->assertSame(1, CorteCajaMovimientos::where('idVenta', $venta->ventaId)->count());
    }

    public function test_cobrar_con_menos_dinero_no_cierra()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $this->ventas->agregarProducto($venta, $this->producto(['price' => 25]), 2, null, $this->mesero->id);

        $this->esperarError(function () use ($venta) {
            $this->ventas->cobrar($venta->fresh(), 49.99, Venta::PAGO_EFECTIVO, $this->mesero->id);
        }, 'quantity');
        $this->esperarError(function () use ($venta) {
            $this->ventas->cobrar($venta->fresh(), 50, 9, $this->mesero->id);
        }, 'paymenttype');

        $this->assertTrue($venta->fresh()->estaAbierta());
    }

    public function test_cobrar_con_descuento_registra_el_monto_con_descuento()
    {
        $this->corteAbierto($this->mesero);
        $venta = $this->ventaAbierta($this->mesero, [
            'apply_discount' => 1, 'montoTotalDescuento' => 180,
        ]);
        $this->ventas->agregarProducto($venta, $this->producto(['price' => 200]), 1, null, $this->mesero->id);

        $this->esperarError(function () use ($venta) {
            $this->ventas->cobrar($venta->fresh(), 179, Venta::PAGO_TRANSFERENCIA, $this->mesero->id);
        }, 'quantity');
        $this->ventas->cobrar($venta->fresh(), 180, Venta::PAGO_TRANSFERENCIA, $this->mesero->id);

        $this->assertEquals(180, CorteCajaMovimientos::where('idVenta', $venta->ventaId)->value('monto'));
    }
}
