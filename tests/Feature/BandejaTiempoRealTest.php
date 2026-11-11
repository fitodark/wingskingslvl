<?php

namespace Tests\Feature;

use App\Client;
use App\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Refresco automático de la bandeja (GET /comandas/bandeja, docs/plan-bandeja-tiempo-real.md).
 * Las ventas del agente se insertan con SQL directo, igual que lo hace el agente de WhatsApp.
 */
class BandejaTiempoRealTest extends TestCase
{
    use RefreshDatabase;

    private $mesero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mesero = $this->usuario('mesero');
        $this->config('discountPercentage', '0');
    }

    private function consultar(array $params = [])
    {
        return $this->actingAs($this->mesero)->get(route('bandeja', $params));
    }

    /** INSERT directo en ventas + ventasproductos, como agent/tools.py. */
    private function ventaDelAgente(array $datos = [])
    {
        $cliente = Client::create([
            'name' => 'Ana WhatsApp', 'phone' => '5500000404', 'address' => 'Calle 4',
            'reference' => 'Portón café', 'active' => 1,
        ]);
        $producto = $this->producto();
        $ahora = now();
        $folio = DB::table('ventas')->insertGetId(array_merge([
            'IdUsuario' => $this->mesero->id, 'client_id' => $cliente->id, 'montoTotal' => 150,
            'montoSubtotal' => 0, 'montoIva' => 0, 'cantidadRecibida' => 0, 'cantidadProductos' => 1,
            'type' => Venta::TIPO_DOMICILIO, 'estatus' => Venta::ESTATUS_ABIERTA, 'activo' => 1,
            'order' => 1, 'apply_discount' => 0, 'venta_agente' => 1, 'tipo_envio' => Venta::ENVIO_EXPRESS,
            'monto_envio_express' => 45, 'colonia_envio' => 'Acatlima',
            'created_at' => $ahora, 'updated_at' => $ahora,
        ], $datos));
        DB::table('ventasproductos')->insert([
            'IdProducto' => $producto->id, 'IdVenta' => $folio, 'cantidad' => 1, 'montoVenta' => 150,
            'order' => 1, 'estatus' => 1, 'delete_flag' => 0, 'id_user_create' => $this->mesero->id,
            'created_at' => $ahora, 'updated_at' => $ahora,
        ]);
        return $folio;
    }

    public function test_sin_cambios_responde_204()
    {
        $this->ventaAbierta($this->mesero, ['activo' => 1]);
        $firma = $this->consultar()->assertOk()->json('firma');

        $this->consultar(['firma' => $firma])->assertStatus(204);
    }

    public function test_venta_del_agente_insertada_por_sql_cambia_la_bandeja_y_se_avisa()
    {
        $inicio = $this->consultar()->assertOk();
        $folio = $this->ventaDelAgente();

        $respuesta = $this->consultar(['firma' => $inicio->json('firma'), 'desde' => $inicio->json('maxFolio')])
            ->assertOk();

        $this->assertNotSame($inicio->json('firma'), $respuesta->json('firma'));
        $this->assertSame($folio, $respuesta->json('maxFolio'));
        $this->assertStringContainsString('data-folio="' . $folio . '"', $respuesta->json('html'));
        $this->assertStringContainsString('Cobrar', $respuesta->json('html')); // con acciones
        $this->assertSame([[
            'folio' => $folio, 'cliente' => 'Ana WhatsApp', 'tipo' => 'Domicilio', 'express' => true,
        ]], $respuesta->json('nuevas'));

        // Ya conocida: no se vuelve a avisar.
        $this->assertSame([], $this->consultar(['desde' => $folio])->json('nuevas'));
    }

    public function test_venta_del_pos_refresca_la_tabla_pero_no_avisa()
    {
        $inicio = $this->consultar();
        $venta = $this->ventaAbierta($this->mesero, ['activo' => 1]);

        $respuesta = $this->consultar(['firma' => $inicio->json('firma'), 'desde' => $inicio->json('maxFolio')])
            ->assertOk();

        $this->assertStringContainsString('data-folio="' . $venta->ventaId . '"', $respuesta->json('html'));
        $this->assertSame([], $respuesta->json('nuevas'));
    }

    public function test_cambios_en_productos_o_cobro_cambian_la_firma()
    {
        $folio = $this->ventaDelAgente();
        $firma = $this->consultar()->json('firma');

        DB::table('ventasproductos')->where('IdVenta', $folio)->update(['delete_flag' => 1]);
        $firma2 = $this->consultar(['firma' => $firma])->assertOk()->json('firma');
        $this->assertNotSame($firma, $firma2);

        DB::table('ventas')->where('ventaId', $folio)->update(['estatus' => Venta::ESTATUS_CERRADA]);
        $respuesta = $this->consultar(['firma' => $firma2])->assertOk();
        $this->assertStringNotContainsString('data-folio="' . $folio . '"', $respuesta->json('html'));
    }

    public function test_la_paginacion_apunta_a_comandas()
    {
        for ($i = 0; $i < 11; $i++) {
            $this->ventaAbierta($this->mesero, ['activo' => 1]);
        }

        $html = $this->consultar(['page' => 2])->assertOk()->json('html');

        $this->assertStringContainsString(route('comandas') . '?page=1', $html);
        $this->assertStringNotContainsString('/comandas/bandeja?page', $html);
    }

    public function test_requiere_sesion()
    {
        $this->getJson(route('bandeja'))->assertStatus(401);
    }
}
