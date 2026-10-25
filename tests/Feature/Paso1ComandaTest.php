<?php

namespace Tests\Feature;

use App\Client;
use App\Dinerstable;
use App\Services\VentaService;
use App\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Paso 1 de la comanda: para dónde es (mesa, domicilio, para llevar) y búsqueda de direcciones.
 */
class Paso1ComandaTest extends TestCase
{
    use RefreshDatabase;

    private $mesero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mesero = $this->usuario('mesero');
        $this->config('printStatus', 'false');
        $this->config('discountPercentage', '0');
    }

    private function mesa($nombre)
    {
        $mesa = new Dinerstable();
        $mesa->name = $nombre;
        $mesa->detail = '';
        $mesa->save();
        return $mesa;
    }

    private function cliente(array $datos = [])
    {
        return Client::create(array_merge([
            'name' => 'Ana Ejemplo', 'phone' => '5500000101', 'address' => 'Calle Uno 1',
            'reference' => 'Portón verde', 'active' => 1,
        ], $datos));
    }

    private function actualizar(Venta $venta, array $datos)
    {
        return $this->actingAs($this->mesero)->put(route('update', ['venta' => $venta->ventaId]), $datos);
    }

    public function test_venta_nueva_sin_tipo_preseleccionado()
    {
        $this->mesa('Mesa 1');
        $venta = $this->ventaAbierta($this->mesero);

        $html = $this->actingAs($this->mesero)->get(route('create', [$venta->ventaId]))->assertOk()->getContent();

        $this->assertNotRegExp('/name="type"[^>]*checked/', $html);
        $this->assertNotRegExp('/name="table"[^>]*checked/', $html);
        $this->assertStringContainsString('id="wkSinTipo" class="wk-vacio" >', $html);
    }

    public function test_mesa_muestra_cuentas_abiertas_de_otras_ventas()
    {
        $mesa7 = $this->mesa('Mesa 7');
        $this->ventaAbierta($this->mesero, ['dinerstable_id' => $mesa7->id, 'activo' => 1]);
        $this->ventaAbierta($this->mesero, ['dinerstable_id' => $mesa7->id, 'activo' => 1, 'nombre_cuenta' => 'Cuenta de Ana']);
        // Un borrador del paso 1 (activo = 0) no cuenta como cuenta abierta.
        $this->ventaAbierta($this->mesero, ['dinerstable_id' => $mesa7->id, 'activo' => 0]);
        $venta = $this->ventaAbierta($this->mesero);

        $this->actingAs($this->mesero)->get(route('create', [$venta->ventaId]))
            ->assertSee('2 cuentas abiertas')
            ->assertSee('Cuenta de Ana');
    }

    public function test_el_tipo_es_obligatorio_y_mesa_no_guarda_cliente()
    {
        $mesa = $this->mesa('Mesa 3');
        $cliente = $this->cliente();
        $venta = $this->ventaAbierta($this->mesero);

        $this->actualizar($venta, ['table' => $mesa->id])->assertSessionHasErrors('type');

        $this->actualizar($venta, ['type' => Venta::TIPO_LOCAL, 'table' => $mesa->id, 'clientId' => $cliente->id])
            ->assertSessionHasNoErrors();
        $venta = $venta->fresh();
        $this->assertSame($mesa->id, (int) $venta->dinerstable_id);
        $this->assertNull($venta->client_id);
    }

    public function test_domicilio_guarda_la_direccion_elegida_sin_tocar_la_del_agente()
    {
        $casa = $this->cliente(['address' => 'Calle Uno 1']);
        $oficina = $this->cliente(['address' => 'Av. Trabajo 99', 'reference' => 'Edificio azul']);
        $venta = $this->ventaAbierta($this->mesero, ['direccion_envio' => 'Dirección dictada por WhatsApp']);

        $this->actualizar($venta, ['type' => Venta::TIPO_DOMICILIO])->assertSessionHasErrors('ubicacion');
        $this->actualizar($venta, ['type' => Venta::TIPO_DOMICILIO, 'clientId' => 999999])->assertSessionHasErrors('clientId');

        $this->actualizar($venta, ['type' => Venta::TIPO_DOMICILIO, 'clientId' => $oficina->id])->assertSessionHasNoErrors();
        $venta = $venta->fresh();
        $this->assertSame($oficina->id, (int) $venta->client_id);
        $this->assertSame('Dirección dictada por WhatsApp', $venta->direccion_envio);
        $this->assertNotSame($casa->id, (int) $venta->client_id);
    }

    public function test_para_llevar_con_nombre_o_cliente()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $this->actualizar($venta, ['type' => Venta::TIPO_PARA_LLEVAR])->assertSessionHasErrors('ubicacion');
        $this->actualizar($venta, ['type' => Venta::TIPO_PARA_LLEVAR, 'nombre_cuenta' => 'Juan'])->assertSessionHasNoErrors();
        $this->assertSame('Juan', $venta->fresh()->nombre_cuenta);
    }

    public function test_busqueda_de_clientes_limitada_y_solo_activos()
    {
        $this->cliente(['name' => 'Ana Activa', 'phone' => '5511112222']);
        $this->cliente(['name' => 'Ana Inactiva', 'phone' => '5511113333', 'active' => 0]);
        for ($i = 0; $i < 25; $i++) {
            $this->cliente(['name' => "Cliente $i", 'phone' => '5599' . str_pad($i, 6, '0', STR_PAD_LEFT)]);
        }

        $this->actingAs($this->mesero)->getJson(route('getQueryClient') . '?query=' . urlencode('a'))->assertExactJson([]);

        $nombres = collect($this->actingAs($this->mesero)->getJson(route('getQueryClient') . '?query=' . urlencode('ana'))->json())
            ->pluck('name')->all();
        $this->assertSame(['Ana Activa'], $nombres);

        // Teléfono con espacios o guiones también encuentra; máximo 20.
        $this->assertCount(1, $this->actingAs($this->mesero)->getJson(route('getQueryClient') . '?query=' . urlencode('55 1111-2'))->json());
        $this->assertCount(20, $this->actingAs($this->mesero)->getJson(route('getQueryClient') . '?query=' . urlencode('5599'))->json());
    }

    public function test_direcciones_del_mismo_telefono()
    {
        $this->cliente(['address' => 'Calle Uno 1']);
        $this->cliente(['address' => 'Av. Trabajo 99']);
        $this->cliente(['address' => 'Vieja', 'active' => 0]);
        $this->cliente(['phone' => '5500009999', 'address' => 'Otro teléfono']);

        $direcciones = collect($this->actingAs($this->mesero)
            ->getJson(route('direccionesCliente', ['phone' => '5500000101']))->json())->pluck('address')->sort()->values()->all();
        $this->assertSame(['Av. Trabajo 99', 'Calle Uno 1'], $direcciones);
    }

    public function test_agregar_cliente_desde_el_paso_1_conserva_el_tipo()
    {
        $venta = $this->ventaAbierta($this->mesero);

        $this->actingAs($this->mesero)->post(route('addClient'), [
            'ventaid' => $venta->ventaId, 'type' => Venta::TIPO_DOMICILIO,
            'name' => 'Nuevo', 'phone' => '5512345678', 'address' => 'Calle Nueva 5', 'reference' => 'Esquina',
        ])->assertRedirect();

        $venta = $venta->fresh();
        $this->assertSame(Venta::TIPO_DOMICILIO, (int) $venta->type);
        $this->assertNotNull($venta->client_id);

        // Al recargar el paso 1 aparece Domicilio elegido y la ficha del cliente.
        $this->actingAs($this->mesero)->get(route('create', [$venta->ventaId, $venta->client_id]))
            ->assertSee('id="tipo2" value="2"' . "\n                        checked", false)
            ->assertSee('Calle Nueva 5');
    }
}
