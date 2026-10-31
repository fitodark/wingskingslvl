<?php

namespace Tests\Feature;

use App\Client;
use App\Services\EnvioService;
use App\TarifaEnvio;
use App\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Envío express en el paso 1 (comandas capturadas a mano, plan docs/plan-envio-express-paso1.md).
 * Colonias del catálogo cargado por la migración: Acatlima (solo express, $45),
 * Chapultepec (gratuito sin tarifa express).
 */
class EnvioPaso1Test extends TestCase
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

    private function cliente($colonia, array $datos = [])
    {
        return Client::create(array_merge([
            'name' => 'Juan Pérez', 'phone' => '5500000303', 'address' => 'Calle 3',
            'reference' => 'Zaguán negro', 'colonia' => $colonia, 'active' => 1,
        ], $datos));
    }

    private function actualizar(Venta $venta, array $datos)
    {
        return $this->actingAs($this->mesero)->put(route('update', ['venta' => $venta->ventaId]), $datos);
    }

    private function domicilio(Client $cliente, array $datos = [])
    {
        return array_merge(['type' => Venta::TIPO_DOMICILIO, 'clientId' => $cliente->id], $datos);
    }

    public function test_buscar_tarifa_ignora_mayusculas_y_acentos()
    {
        $envio = new EnvioService();
        $this->assertSame('Acatlima', $envio->buscarTarifa('  acatlima ')->colonia);
        $this->assertSame('Cuauhtémoc', $envio->buscarTarifa('CUAUHTEMOC')->colonia);
        $this->assertNull($envio->buscarTarifa('Colonia que no existe'));
        $this->assertNull($envio->buscarTarifa(null));

        TarifaEnvio::where('colonia', 'Acatlima')->update(['activo' => false]);
        $this->assertNull($envio->buscarTarifa('Acatlima'));
    }

    public function test_solo_express_exige_aceptar_y_el_monto_sale_del_catalogo()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $cliente = $this->cliente('Acatlima');

        $this->actualizar($venta, $this->domicilio($cliente))
            ->assertSessionHasErrors(['envio' => 'El cliente debe aceptar el envío express para continuar.']);
        $this->assertNull($venta->fresh()->tipo_envio);

        // El formulario no puede mandar otro monto: se lee de la tabla.
        $this->actualizar($venta, $this->domicilio($cliente, ['acepta_express' => '1', 'monto_envio_express' => '1']))
            ->assertSessionHasNoErrors();
        $venta = $venta->fresh();
        $this->assertSame(Venta::ENVIO_EXPRESS, (int) $venta->tipo_envio);
        $this->assertEquals(45, $venta->monto_envio_express);
        $this->assertSame('Acatlima', $venta->colonia_envio);
        $this->assertEquals(0, $venta->montoTotal);
    }

    public function test_gratuito_por_defecto_y_express_solo_si_hay_tarifa()
    {
        $venta = $this->ventaAbierta($this->mesero);
        $cliente = $this->cliente('Chapultepec');

        $this->actualizar($venta, $this->domicilio($cliente, ['envio' => 'express']))
            ->assertSessionHasErrors(['envio' => 'Esta colonia no tiene tarifa express.']);

        $this->actualizar($venta, $this->domicilio($cliente))->assertSessionHasNoErrors();
        $venta = $venta->fresh();
        $this->assertSame(Venta::ENVIO_GRATUITO, (int) $venta->tipo_envio);
        $this->assertNull($venta->monto_envio_express);
        $this->assertSame('Chapultepec', $venta->colonia_envio);

        // Gratuito con tarifa express: el usuario puede elegir express.
        TarifaEnvio::where('colonia', 'Chapultepec')->update(['monto_express' => 35]);
        $this->actualizar($venta, $this->domicilio($cliente, ['envio' => 'express']))->assertSessionHasNoErrors();
        $this->assertEquals(35, $venta->fresh()->monto_envio_express);
    }

    public function test_sin_colonia_del_catalogo_no_pasa()
    {
        $venta = $this->ventaAbierta($this->mesero);

        $this->actualizar($venta, $this->domicilio($this->cliente(null)))
            ->assertSessionHasErrors(['envio' => 'La dirección del cliente no tiene una colonia del catálogo de envío.']);
        $this->actualizar($venta, $this->domicilio($this->cliente('Colonia Inventada')))
            ->assertSessionHasErrors('envio');
    }

    public function test_para_llevar_y_mesa_dejan_el_envio_en_null()
    {
        $cliente = $this->cliente('Acatlima');
        $venta = $this->ventaAbierta($this->mesero, [
            'type' => Venta::TIPO_DOMICILIO, 'client_id' => $cliente->id,
            'tipo_envio' => Venta::ENVIO_EXPRESS, 'monto_envio_express' => 45, 'colonia_envio' => 'Acatlima',
        ]);

        $this->actualizar($venta, ['type' => Venta::TIPO_PARA_LLEVAR, 'clientId' => $cliente->id])->assertSessionHasNoErrors();
        $venta = $venta->fresh();
        $this->assertNull($venta->tipo_envio);
        $this->assertNull($venta->monto_envio_express);
        $this->assertNull($venta->colonia_envio);
        $this->assertSame($cliente->id, (int) $venta->client_id);

        $this->actualizar($venta, ['type' => Venta::TIPO_LOCAL, 'nombre_cuenta' => 'Ana'])->assertSessionHasNoErrors();
        $this->assertNull($venta->fresh()->tipo_envio);
    }

    public function test_venta_del_agente_conserva_su_envio_mientras_no_cambie_el_cliente()
    {
        // El cliente ya no tiene colonia en su registro: lo aceptado por WhatsApp manda.
        $cliente = $this->cliente(null);
        $venta = $this->ventaAbierta($this->mesero, [
            'type' => Venta::TIPO_DOMICILIO, 'client_id' => $cliente->id, 'venta_agente' => 1,
            'tipo_envio' => Venta::ENVIO_EXPRESS, 'monto_envio_express' => 50, 'colonia_envio' => 'Agua Dulce',
        ]);

        $this->actualizar($venta, $this->domicilio($cliente))->assertSessionHasNoErrors();
        $venta = $venta->fresh();
        $this->assertSame(Venta::ENVIO_EXPRESS, (int) $venta->tipo_envio);
        $this->assertEquals(50, $venta->monto_envio_express);
        $this->assertSame('Agua Dulce', $venta->colonia_envio);

        // Otro cliente (otra dirección): se vuelve a calcular con su colonia.
        $otro = $this->cliente('Chapultepec', ['address' => 'Calle 4']);
        $this->actualizar($venta, $this->domicilio($otro))->assertSessionHasNoErrors();
        $venta = $venta->fresh();
        $this->assertSame(Venta::ENVIO_GRATUITO, (int) $venta->tipo_envio);
        $this->assertSame('Chapultepec', $venta->colonia_envio);
    }

    public function test_endpoint_de_tarifa_por_cliente()
    {
        $this->actingAs($this->mesero)->getJson(route('tarifaEnvio', [$this->cliente('acatlima')->id]))
            ->assertOk()
            ->assertExactJson(['encontrada' => true, 'colonia' => 'Acatlima', 'envio_gratuito' => false,
                'express_disponible' => true, 'monto_express' => 45]);

        $this->actingAs($this->mesero)->getJson(route('tarifaEnvio', [$this->cliente('Por ahí')->id]))
            ->assertOk()
            ->assertExactJson(['encontrada' => false, 'colonia' => 'Por ahí']);
    }

    public function test_actualizar_colonia_rechaza_fuera_del_catalogo_y_guarda_el_nombre_del_catalogo()
    {
        $cliente = $this->cliente(null);

        $this->actingAs($this->mesero)->postJson(route('clienteColonia'), ['clientId' => $cliente->id, 'colonia' => 'Inventada'])
            ->assertJson(['errors' => ['Elige una colonia de la lista.']]);
        $this->assertNull($cliente->fresh()->colonia);

        $this->actingAs($this->mesero)->postJson(route('clienteColonia'), ['clientId' => $cliente->id, 'colonia' => 'acatlima'])
            ->assertJson(['encontrada' => true, 'colonia' => 'Acatlima', 'monto_express' => 45]);
        $this->assertSame('Acatlima', $cliente->fresh()->colonia);
    }

    public function test_formulario_de_clientes_valida_la_colonia_y_los_modales_la_normalizan()
    {
        $admin = $this->usuario('admin');
        $datos = ['name' => 'Eva', 'phone' => '5500000404', 'address' => 'Calle 5', 'reference' => 'Esquina'];

        $this->actingAs($admin)->post(route('clientes.store'), $datos + ['colonia' => 'Inventada'])
            ->assertSessionHasErrors(['colonia' => 'Elige una colonia de la lista.']);
        $this->actingAs($admin)->post(route('clientes.store'), $datos + ['colonia' => 'ACATLIMA'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Acatlima', Client::where('phone', '5500000404')->value('colonia'));

        // Modal del paso 1: no se pierde la captura; la colonia inválida queda vacía y el panel la pide.
        $venta = $this->ventaAbierta($this->mesero);
        $this->actingAs($this->mesero)->post(route('addClient'), [
            'ventaid' => $venta->ventaId, 'type' => Venta::TIPO_DOMICILIO,
            'name' => 'Luis', 'phone' => '5500000505', 'address' => 'Calle 6',
            'colonia' => 'Inventada', 'reference' => 'Casa',
        ])->assertRedirect();
        $this->assertNull(Client::where('phone', '5500000505')->value('colonia'));
    }

    public function test_el_paso_1_trae_el_panel_de_envio_y_el_estado_inicial()
    {
        $cliente = $this->cliente('Acatlima');
        $venta = $this->ventaAbierta($this->mesero, [
            'type' => Venta::TIPO_DOMICILIO, 'client_id' => $cliente->id, 'venta_agente' => 1,
            'tipo_envio' => Venta::ENVIO_EXPRESS, 'monto_envio_express' => 45, 'colonia_envio' => 'Acatlima',
        ]);

        $html = $this->actingAs($this->mesero)->get(route('create', [$venta->ventaId, $cliente->id]))
            ->assertOk()
            ->assertSee('id="panelEnvio"')
            ->assertSee('<datalist id="coloniasPaso1">')
            ->getContent();
        $this->assertStringContainsString('&quot;tipo_envio&quot;:2', $html);
        $this->assertStringContainsString('&quot;agente&quot;:true', $html);
    }
}
