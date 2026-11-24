<?php

namespace App\Services;

use App\Config;
use Carbon\Carbon;

/**
 * Tiempo de entrega estimado a domicilio, el mismo que le da el agente de WhatsApp
 * (whatsapp-agentkit/agent/tools.py: obtener_tiempo_entrega / obtener_tiempo_express).
 *
 * - Gratuito: configs.delivery_time_windows, una fila por ventana con el formato
 *   "<etiqueta> [HH:MM HH:MM] MIN-MAX". Aplica la ventana en la que cae la hora actual;
 *   si ninguna aplica, 40-60.
 * - Express: configs.express_delivery_time, "MIN-MAX" (p. ej. "40-50") o un solo número.
 *   Igual para todas las colonias; solo cambia la tarifa.
 *
 * Es informativo: no se guarda en la venta.
 */
class TiempoEntregaService
{
    const GRATUITO_DEFAULT = [40, 60];
    const EXPRESS_DEFAULT = [40, 50];

    /**
     * @return array ['minutos_min' => int, 'minutos_max' => int, 'texto' => 'entre 40 minutos y 1 hora']
     */
    public function gratuito(Carbon $ahora = null)
    {
        $ahora = $ahora ?: now();
        $minuto = $ahora->hour * 60 + $ahora->minute;
        $rango = self::GRATUITO_DEFAULT;

        foreach ($this->valores('delivery_time_windows') as $valor) {
            if (!preg_match('/\[\s*(\d{1,2}):(\d{2})\s*[-–—]?\s*(\d{1,2}):(\d{2})\s*\]\s*(\d+)\s*[-–—]\s*(\d+)/u', $valor, $m)) {
                continue;
            }
            $abre = (int) $m[1] * 60 + (int) $m[2];
            $cierra = (int) $m[3] * 60 + (int) $m[4];
            if ($abre <= $minuto && $minuto < $cierra) {
                $rango = [(int) $m[5], (int) $m[6]];
                break;
            }
        }
        return $this->resultado($rango);
    }

    /**
     * @return array ['minutos_min' => int, 'minutos_max' => int, 'texto' => 'entre 40 minutos y 50 minutos']
     */
    public function express()
    {
        $valores = $this->valores('express_delivery_time');
        $rango = self::EXPRESS_DEFAULT;
        if ($valores) {
            $rango = self::leerRango($valores[0]) ?: $rango;
        }
        return $this->resultado($rango);
    }

    /**
     * "40-50" → [40, 50]; "30" → [30, 30]; inválido → null. Un rango invertido se ordena.
     */
    public static function leerRango($valor)
    {
        $valor = trim((string) $valor);
        if (preg_match('/^(\d+)\s*[-–—]\s*(\d+)$/u', $valor, $m)) {
            $a = (int) $m[1];
            $b = (int) $m[2];
            return $a > 0 && $b > 0 ? [min($a, $b), max($a, $b)] : null;
        }
        if (preg_match('/^\d+$/', $valor) && (int) $valor > 0) {
            return [(int) $valor, (int) $valor];
        }
        return null;
    }

    /**
     * 40 → "40 minutos"; 60 → "1 hora"; 80 → "1 hora 20 minutos" (mismo texto que el agente).
     */
    public static function formatearMinutos($minutos)
    {
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;
        if ($horas === 0) {
            return $resto . ' minutos';
        }
        $texto = $horas . ' hora' . ($horas > 1 ? 's' : '');
        return $resto ? $texto . ' ' . $resto . ' minutos' : $texto;
    }

    private function resultado(array $rango)
    {
        list($min, $max) = $rango;
        return [
            'minutos_min' => $min,
            'minutos_max' => $max,
            'texto' => $min === $max
                ? self::formatearMinutos($min)
                : 'entre ' . self::formatearMinutos($min) . ' y ' . self::formatearMinutos($max),
        ];
    }

    private function valores($clave)
    {
        return Config::where('key', $clave)->orderBy('order')->orderBy('id')->pluck('value')->all();
    }
}
