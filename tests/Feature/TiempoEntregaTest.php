<?php

namespace Tests\Feature;

use App\Config;
use App\Services\TiempoEntregaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tiempo de entrega estimado (mismo cálculo que el agente de WhatsApp):
 * gratuito por ventana de configs.delivery_time_windows, express por configs.express_delivery_time.
 */
class TiempoEntregaTest extends TestCase
{
    use RefreshDatabase;

    private function ventanas(array $valores)
    {
        Config::where('key', 'delivery_time_windows')->delete();
        foreach ($valores as $i => $valor) {
            Config::create(['key' => 'delivery_time_windows', 'value' => $valor, 'order' => $i + 1]);
        }
    }

    private function a($hora, $minuto)
    {
        return Carbon::create(2026, 11, 23, $hora, $minuto);
    }

    public function test_gratuito_usa_la_ventana_de_la_hora_actual()
    {
        $this->ventanas(['Baja demanda [13:00 20:00] 40-60', 'Alta demanda [20:00 23:00] 60-80', 'Mal escrita 99']);
        $tiempos = new TiempoEntregaService();

        $this->assertSame('entre 40 minutos y 1 hora', $tiempos->gratuito($this->a(13, 0))['texto']);
        $this->assertSame('entre 40 minutos y 1 hora', $tiempos->gratuito($this->a(19, 59))['texto']);
        $alta = $tiempos->gratuito($this->a(20, 0));
        $this->assertSame([60, 80], [$alta['minutos_min'], $alta['minutos_max']]);
        $this->assertSame('entre 1 hora y 1 hora 20 minutos', $alta['texto']);
        // Fuera de las ventanas (o sin ventanas) se usa 40-60, igual que el agente.
        $this->assertSame('entre 40 minutos y 1 hora', $tiempos->gratuito($this->a(23, 30))['texto']);
        $this->ventanas([]);
        $this->assertSame('entre 40 minutos y 1 hora', $tiempos->gratuito($this->a(21, 0))['texto']);
    }

    public function test_express_acepta_rango_o_un_solo_numero()
    {
        $tiempos = new TiempoEntregaService();
        // Valor que deja la migración 2026_11_23_000001.
        $this->assertSame('40-50', Config::where('key', 'express_delivery_time')->value('value'));
        $this->assertSame('entre 40 minutos y 50 minutos', $tiempos->express()['texto']);

        $this->config('express_delivery_time', '45');
        $this->assertSame('45 minutos', $tiempos->express()['texto']);
        $this->config('express_delivery_time', '50 - 40');
        $this->assertSame([40, 50], array_values(array_slice($tiempos->express(), 0, 2)));
        $this->config('express_delivery_time', 'media hora');
        $this->assertSame('entre 40 minutos y 50 minutos', $tiempos->express()['texto']);
        Config::where('key', 'express_delivery_time')->delete();
        $this->assertSame('entre 40 minutos y 50 minutos', $tiempos->express()['texto']);
    }

    public function test_formato_de_minutos()
    {
        $this->assertSame('40 minutos', TiempoEntregaService::formatearMinutos(40));
        $this->assertSame('1 hora', TiempoEntregaService::formatearMinutos(60));
        $this->assertSame('1 hora 20 minutos', TiempoEntregaService::formatearMinutos(80));
        $this->assertSame('2 horas', TiempoEntregaService::formatearMinutos(120));
        $this->assertNull(TiempoEntregaService::leerRango('0-10'));
        $this->assertNull(TiempoEntregaService::leerRango(''));
    }
}
