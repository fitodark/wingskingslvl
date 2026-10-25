<?php

namespace Tests\Feature;

use App\VentasProductos;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Endpoint que usa el agente de WhatsApp para imprimir la orden por folio.
 * El token de prueba sale de phpunit.xml (AGENTE_TOKEN).
 */
class AgenteApiTest extends TestCase
{
    use RefreshDatabase;

    private function imprimir($folio, $token = 'token-de-prueba')
    {
        $headers = $token === null ? [] : ['X-Agente-Token' => $token];
        return $this->postJson("/api/agente/ventas/$folio/imprimir-orden", [], $headers);
    }

    private function ventaDelAgente(array $datos = [])
    {
        return $this->ventaAbierta($this->usuario('mesero'), array_merge(['venta_agente' => 1], $datos));
    }

    public function test_sin_token_o_con_token_incorrecto_es_401()
    {
        $venta = $this->ventaDelAgente();
        $this->imprimir($venta->ventaId, null)->assertStatus(401);
        $this->imprimir($venta->ventaId, 'otro')->assertStatus(401);
    }

    public function test_solo_ventas_abiertas_del_agente()
    {
        $this->config('printStatus', 'true');
        $this->imprimir(999999)->assertStatus(404);

        $delWizard = $this->ventaAbierta($this->usuario('mesero'));
        $this->imprimir($delWizard->ventaId)->assertStatus(404);

        $cerrada = $this->ventaDelAgente(['estatus' => \App\Venta::ESTATUS_CERRADA]);
        $this->imprimir($cerrada->ventaId)->assertStatus(409);
    }

    public function test_impresion_apagada_es_503()
    {
        $this->config('printStatus', 'false');
        $this->imprimir($this->ventaDelAgente()->ventaId)->assertStatus(503);
    }

    public function test_no_imprime_si_los_sabores_no_cuadran()
    {
        $this->config('printStatus', 'true');
        $venta = $this->ventaDelAgente();
        $alitas = $this->alitas(10);
        // El agente inserta directo en la base, sin pasar por la validación del wizard.
        VentasProductos::create([
            'IdVenta' => $venta->ventaId, 'IdProducto' => $alitas->id, 'cantidad' => 1, 'montoVenta' => 150,
            'descripcion' => $this->sabores([['10 Piezas', 'Habanero'], ['10 Piezas', 'Bufalo']]),
            'estatus' => 1, 'delete_flag' => 1, 'order' => 1,
        ]);

        $this->imprimir($venta->ventaId)
            ->assertStatus(422)
            ->assertJson(['success' => false, 'problemas' => [['problema' => 'Sabores suman 20 de 10 piezas']]]);
    }

    public function test_orden_vacia_es_422()
    {
        $this->config('printStatus', 'true');
        $this->imprimir($this->ventaDelAgente()->ventaId)->assertStatus(422);
    }
}
