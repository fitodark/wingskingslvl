<?php

namespace Tests\Feature;

use App\Client;
use App\Config;
use App\TarifaEnvio;
use App\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Envío express por colonia: catálogo de tarifas (admin), etiqueta en la bandeja
 * y colonia del cliente. El monto express es informativo y no toca los totales.
 */
class EnvioExpressTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_migracion_carga_el_catalogo_y_la_clave_de_tiempo()
    {
        $this->assertSame(128, TarifaEnvio::count());
        $this->assertSame(22, TarifaEnvio::where('envio_gratuito', false)->whereNotNull('monto_express')->count());
        $this->assertSame(106, TarifaEnvio::where('envio_gratuito', true)->whereNull('monto_express')->count());
        $this->assertSame('agencia', TarifaEnvio::where('colonia', 'Agencia Municipal Rancho Solano')->value('tipo'));
        $this->assertSame('30', Config::where('key', 'express_delivery_time')->value('value'));
    }

    public function test_solo_admin_entra_a_tarifas()
    {
        $this->actingAs($this->usuario('mesero'))->get(route('tarifasenvio.index'))->assertStatus(403);
        $this->actingAs($this->usuario('encargado'))->get(route('tarifasenvio.index'))->assertStatus(403);
        $this->actingAs($this->usuario('admin'))->get(route('tarifasenvio.index', ['q' => 'acatl']))
            ->assertOk()
            ->assertSee('Acatlima')
            ->assertDontSee('Volcanes');
    }

    public function test_solo_express_exige_tarifa_y_gratuito_sin_tarifa_queda_null()
    {
        $admin = $this->usuario('admin');

        $this->actingAs($admin)->post(route('tarifasenvio.store'), [
            'colonia' => 'Colonia Lejana', 'tipo' => 'colonia', 'envio_gratuito' => '0', 'monto_express' => '',
        ])->assertSessionHasErrors('monto_express');

        $this->actingAs($admin)->post(route('tarifasenvio.store'), [
            'colonia' => 'Colonia Lejana', 'tipo' => 'colonia', 'envio_gratuito' => '0', 'monto_express' => '55',
        ])->assertRedirect(route('tarifasenvio.index'));

        $this->actingAs($admin)->post(route('tarifasenvio.store'), [
            'colonia' => 'Colonia Cercana', 'tipo' => 'colonia', 'envio_gratuito' => '1', 'monto_express' => '',
        ])->assertRedirect(route('tarifasenvio.index'));

        $lejana = TarifaEnvio::where('colonia', 'Colonia Lejana')->first();
        $this->assertFalse($lejana->envio_gratuito);
        $this->assertSame(55.0, $lejana->monto_express);
        $this->assertTrue($lejana->activo);
        $this->assertNull(TarifaEnvio::where('colonia', 'Colonia Cercana')->first()->monto_express);
    }

    public function test_no_permite_colonias_repetidas_salvo_la_misma_al_editar()
    {
        $admin = $this->usuario('admin');
        $acatlima = TarifaEnvio::where('colonia', 'Acatlima')->first();

        $this->actingAs($admin)->post(route('tarifasenvio.store'), [
            'colonia' => 'Acatlima', 'tipo' => 'colonia', 'envio_gratuito' => '0', 'monto_express' => '45',
        ])->assertSessionHasErrors('colonia');

        $this->actingAs($admin)->put(route('tarifasenvio.update', $acatlima->id), [
            'colonia' => 'Acatlima', 'tipo' => 'colonia', 'envio_gratuito' => '0', 'monto_express' => '60',
        ])->assertRedirect(route('tarifasenvio.index'));

        $this->assertSame(60.0, $acatlima->fresh()->monto_express);
    }

    public function test_desactivar_y_activar_en_lugar_de_borrar()
    {
        $admin = $this->usuario('admin');
        $tarifa = TarifaEnvio::where('colonia', 'Morelos')->first();

        $this->actingAs($admin)->post(route('tarifasenvio.activo', $tarifa->id))->assertRedirect();
        $this->assertFalse($tarifa->fresh()->activo);
        $this->assertSame(127, TarifaEnvio::activas()->count());

        $this->actingAs($admin)->post(route('tarifasenvio.activo', $tarifa->id));
        $this->assertTrue($tarifa->fresh()->activo);
    }

    public function test_la_bandeja_muestra_el_tipo_de_envio_sin_sumarlo_al_total()
    {
        $mesero = $this->usuario('mesero');
        $this->corteAbierto($mesero);
        $cliente = Client::create(['name' => 'Ana', 'phone' => '5500000101', 'address' => 'Calle 1',
            'reference' => 'Portón', 'active' => 1]);

        $express = $this->ventaAbierta($mesero, [
            'type' => Venta::TIPO_DOMICILIO, 'client_id' => $cliente->id, 'activo' => 1, 'venta_agente' => 1,
            'montoTotal' => 300, 'tipo_envio' => Venta::ENVIO_EXPRESS, 'monto_envio_express' => 45,
            'colonia_envio' => 'Acatlima',
        ]);
        $this->ventaAbierta($mesero, [
            'type' => Venta::TIPO_DOMICILIO, 'client_id' => $cliente->id, 'activo' => 1, 'venta_agente' => 1,
            'tipo_envio' => Venta::ENVIO_GRATUITO,
        ]);

        $this->actingAs($mesero)->get(route('comandas'))
            ->assertOk()
            ->assertSee('Envío express · $ 45.00 · Acatlima')
            ->assertSee('Envío gratuito');

        $this->assertEquals(300, $express->fresh()->montoTotal);
    }

    public function test_alta_de_cliente_guarda_la_colonia_y_el_formulario_la_ofrece()
    {
        $mesero = $this->usuario('mesero');
        $venta = $this->ventaAbierta($mesero);

        $this->actingAs($mesero)->get(route('create', [$venta->ventaId]))
            ->assertOk()
            ->assertSee('id="coloniasEnvio1"', false)
            ->assertSee('id="coloniasEnvio2"', false)
            ->assertSee('<option value="Acatlima">', false);

        $this->actingAs($mesero)->post(route('addClient'), [
            'ventaid' => $venta->ventaId, 'type' => Venta::TIPO_DOMICILIO,
            'name' => 'Luis', 'phone' => '5500000202', 'address' => 'Calle 2',
            'colonia' => 'Acatlima', 'reference' => 'Casa azul',
        ])->assertRedirect();

        $this->assertSame('Acatlima', Client::where('phone', '5500000202')->value('colonia'));
    }
}
