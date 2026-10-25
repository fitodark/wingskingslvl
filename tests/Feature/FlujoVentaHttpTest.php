<?php

namespace Tests\Feature;

use App\Services\VentaService;
use App\Venta;
use App\VentasProductos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El flujo de venta a través de las rutas: permisos, PIN y respuestas que espera el JS.
 */
class FlujoVentaHttpTest extends TestCase
{
    use RefreshDatabase;

    private $mesero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mesero = $this->usuario('mesero');
        $this->config('printStatus', 'false');
    }

    private function ajax()
    {
        return ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'];
    }

    public function test_mesero_no_entra_a_corte_ni_a_usuarios()
    {
        $this->actingAs($this->mesero)->get('/cortecaja')->assertStatus(403);
        $this->actingAs($this->mesero)->get('/usuarios/create')->assertStatus(403);
    }

    public function test_usuario_inactivo_es_sacado()
    {
        $this->mesero->status = false;
        $this->mesero->save();

        $this->actingAs($this->mesero)->get('/comandas')->assertRedirect(route('login'));
    }

    public function test_agregar_producto_con_cantidad_invalida_regresa_con_error()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $agua = $this->producto();

        $this->actingAs($this->mesero)
            ->post(route('addProductVenta'), [
                'ventaId' => $venta->ventaId, 'idProduct' => $agua->id, 'cantidad' => 0, 'tab' => 'drinks',
            ])
            ->assertRedirect(route('drinksTab', [$venta->ventaId]))
            ->assertSessionHasErrors('cantidad');

        $this->actingAs($this->mesero)
            ->post(route('addProductVenta'), [
                'ventaId' => $venta->ventaId, 'idProduct' => $agua->id, 'cantidad' => 2, 'tab' => 'drinks',
                // El precio del formulario se ignora.
                'price' => 1,
            ])
            ->assertSessionHasNoErrors();
        $this->assertEquals(50, $venta->fresh()->montoTotal);
    }

    public function test_eliminar_comandado_pide_pin_valido_de_un_usuario_activo()
    {
        $this->usuario('encargado', '4321');
        $inactivo = $this->usuario('admin', '9999');
        $inactivo->status = false;
        $inactivo->save();

        $venta = $this->ventaAbierta($this->mesero);
        $ventas = app(VentaService::class);
        $vp = $ventas->agregarProducto($venta, $this->producto(), 1, null, $this->mesero->id);
        $ventas->comandar($venta->fresh(), null, 0);
        $datos = ['productoid' => $vp->ventasProductosId, 'tab' => 'drinks'];

        $this->actingAs($this->mesero)
            ->post(route('eliminarProducto'), $datos + ['pin' => '0000'], $this->ajax())
            ->assertJson(['errors' => ['pin' => 'El PIN es incorrecto!']]);
        // El PIN de un usuario inactivo no sirve.
        $this->actingAs($this->mesero)
            ->post(route('eliminarProducto'), $datos + ['pin' => '9999'], $this->ajax())
            ->assertJsonStructure(['errors' => ['pin']]);
        $this->assertTrue($vp->fresh()->estaActivo());

        $this->actingAs($this->mesero)
            ->post(route('eliminarProducto'), $datos + ['pin' => '4321'], $this->ajax())
            ->assertJson(['success' => true, 'url' => route('drinksTab', [$venta->ventaId])]);
        $this->assertFalse($vp->fresh()->estaActivo());
    }

    public function test_pin_se_bloquea_tras_cinco_intentos()
    {
        $this->usuario('encargado', '4321');
        $venta = $this->ventaAbierta($this->mesero);
        $ventas = app(VentaService::class);
        $vp = $ventas->agregarProducto($venta, $this->producto(), 1, null, $this->mesero->id);
        $ventas->comandar($venta->fresh(), null, 0);
        $datos = ['productoid' => $vp->ventasProductosId, 'tab' => 'drinks'];

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($this->mesero)->post(route('eliminarProducto'), $datos + ['pin' => '0000'], $this->ajax());
        }
        $respuesta = $this->actingAs($this->mesero)
            ->post(route('eliminarProducto'), $datos + ['pin' => '4321'], $this->ajax());

        $this->assertStringContainsString('Demasiados intentos', $respuesta->json('errors.pin'));
        $this->assertTrue($vp->fresh()->estaActivo());
    }

    public function test_eliminar_sin_comandar_no_pide_pin()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $vp = app(VentaService::class)->agregarProducto($venta, $this->producto(), 1, null, $this->mesero->id);

        $this->actingAs($this->mesero)
            ->post(route('eliminarProducto'), ['productoid' => $vp->ventasProductosId, 'tab' => 'foods'])
            ->assertRedirect(route('foodsTab', [$venta->ventaId]));
        $this->assertNull(VentasProductos::find($vp->ventasProductosId));
    }

    public function test_mesero_cobra_desde_la_bandeja()
    {
        $this->corteAbierto($this->mesero);
        $venta = $this->ventaAbierta($this->mesero);
        app(VentaService::class)->agregarProducto($venta, $this->producto(['price' => 40]), 1, null, $this->mesero->id);

        $this->actingAs($this->mesero)
            ->post(route('cerrarVenta'), ['ventaid' => $venta->ventaId, 'quantity' => 30, 'paymenttype' => 1], $this->ajax())
            ->assertJson(['errors' => ['La cantidad ingresada es menor al Monto Total']]);
        $this->actingAs($this->mesero)
            ->post(route('cerrarVenta'), ['ventaid' => $venta->ventaId, 'quantity' => 50], $this->ajax())
            ->assertJson(['errors' => ['Selecciona el método de pago.']]);

        $this->actingAs($this->mesero)
            ->post(route('cerrarVenta'), ['ventaid' => $venta->ventaId, 'quantity' => 50, 'paymenttype' => 2], $this->ajax())
            ->assertJson(['success' => true, 'url' => route('comandas')]);
        $this->assertTrue($venta->fresh()->estaCerrada());

        $this->actingAs($this->mesero)
            ->post(route('cerrarVenta'), ['ventaid' => $venta->ventaId, 'quantity' => 50, 'paymenttype' => 2], $this->ajax())
            ->assertJson(['errors' => ['Esta venta ya fue cerrada.']]);
    }

    public function test_detalle_desde_la_bandeja_es_solo_consulta()
    {
        $this->corteAbierto($this->usuario('admin'));
        $ventas = app(VentaService::class);
        $venta = $this->ventaAbierta($this->mesero, ['activo' => 1]);
        $ventas->agregarProducto($venta, $this->producto(), 1, null, $this->mesero->id);
        $ventas->comandar($venta->fresh(), null, 0);

        $this->actingAs($this->mesero)->get('/comandas')
            ->assertSee(route('detalleComanda', $venta->ventaId));

        // Ronda ya comandada: nada en verde, sin Finalizar, sin Regresar que descarte, sin Continuar.
        $html = $this->actingAs($this->mesero)->get(route('detalleComanda', $venta->ventaId))
            ->assertOk()
            ->assertSee('Volver a la bandeja')
            ->assertDontSee('Continuar ronda')
            ->getContent();
        $this->assertStringNotContainsString('>Finalizar</button>', $html);
        $this->assertStringNotContainsString('form="cancelarVentaForm"', $html);
        $this->assertStringNotContainsString('table-success', $html);

        // Ronda capturada sin mandar a cocina: se puede retomar.
        $ventas->nuevaOrden($venta->fresh());
        $ventas->agregarProducto($venta->fresh(), $this->producto(['name' => 'Refresco']), 1, null, $this->mesero->id);
        $html = $this->actingAs($this->mesero)->get(route('detalleComanda', $venta->ventaId))
            ->assertSee('Continuar ronda')
            ->getContent();
        $this->assertSame(1, substr_count($html, 'table-success'));
        $this->assertStringNotContainsString('>Finalizar</button>', $html);
    }

    public function test_bandeja_muestra_tipo_y_pago_con_su_nombre()
    {
        $this->corteAbierto($this->usuario('admin'));
        $this->ventaAbierta($this->mesero, [
            'type' => Venta::TIPO_PARA_LLEVAR, 'nombre_cuenta' => 'Cuenta de Patricia', 'activo' => 1,
        ]);

        $this->actingAs($this->mesero)->get('/comandas')
            ->assertOk()
            ->assertSee('Para llevar')
            ->assertSee('Cuenta de Patricia');
    }
}
