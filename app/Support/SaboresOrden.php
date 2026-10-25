<?php

namespace App\Support;

use App\Config;

/**
 * Reglas de los sabores de una orden de alitas/boneless.
 *
 * Formato guardado en ventasproductos.descripcion (lo usan el wizard, el agente,
 * la impresión y las vistas, por eso no se cambia):
 *   [[{"key":"","value":"Cantidad"},{"key":"","value":"Sabor"}],   <- encabezado
 *    [{"key":"1","value":"10 Piezas"},{"key":"1","value":"Habanero"}], ...]
 * "key" es la posición en la lista de configs (pieces / flavors) ordenada por "order".
 */
class SaboresOrden
{
    /**
     * Renglones (sin encabezado) como [['cantidad' => '10 Piezas', 'sabor' => 'Habanero'], ...].
     * Regresa null si el texto no tiene el formato esperado.
     */
    public static function renglones($descripcion)
    {
        $datos = json_decode((string) $descripcion, true);
        if (!is_array($datos) || count($datos) < 1) {
            return null;
        }

        $renglones = [];
        foreach (array_slice($datos, 1) as $fila) {
            if (!isset($fila[0]['value'], $fila[1]['value'])) {
                return null;
            }
            $renglones[] = ['cantidad' => (string) $fila[0]['value'], 'sabor' => (string) $fila[1]['value']];
        }
        return $renglones;
    }

    /**
     * Piezas que representa una cantidad: "10 Piezas" = 10, "Todas" = todas, "Mitad" = la mitad.
     */
    public static function piezasDeCantidad($cantidad, $piezasProducto)
    {
        if ($cantidad === 'Todas') {
            return (float) $piezasProducto;
        }
        if ($cantidad === 'Mitad') {
            return $piezasProducto / 2;
        }
        if (preg_match('/^(\d+)/', $cantidad, $m)) {
            return (float) $m[1];
        }
        return null;
    }

    /**
     * Suma de piezas de los renglones; null si alguna cantidad no se reconoce.
     */
    public static function sumaPiezas(array $renglones, $piezasProducto)
    {
        $suma = 0;
        foreach ($renglones as $r) {
            $piezas = self::piezasDeCantidad($r['cantidad'], $piezasProducto);
            if ($piezas === null) {
                return null;
            }
            $suma += $piezas;
        }
        return $suma;
    }

    /**
     * Revisa una orden ya guardada (por ejemplo, la que insertó el agente).
     * Regresa null si está bien o no aplica, o un texto con el problema.
     */
    public static function problema($descripcion, $piezasProducto)
    {
        $renglones = self::renglones($descripcion);
        if ($renglones === null || count($renglones) == 0) {
            return 'Sin sabores';
        }
        if (empty($piezasProducto)) {
            return null;
        }
        $suma = self::sumaPiezas($renglones, $piezasProducto);
        if ($suma === null) {
            return 'Cantidad no reconocida';
        }
        if (abs($suma - $piezasProducto) > 0.001) {
            return 'Sabores suman ' . self::formato($suma) . ' de ' . $piezasProducto . ' piezas';
        }
        return null;
    }

    /**
     * Valida lo que manda el wizard y lo regresa en el formato guardado.
     * Solo acepta cantidades y sabores del catálogo; el texto se toma del catálogo,
     * no de lo que manda el navegador.
     *
     * @return array [string|null $descripcion, string|null $error]
     */
    public static function normalizar($descripcion, $piezasProducto)
    {
        $renglones = self::renglones($descripcion);
        if ($renglones === null || count($renglones) == 0) {
            return [null, 'Agrega al menos un sabor.'];
        }

        $cantidades = Config::where('key', 'pieces')->orderBy('order')->pluck('value')->values()->all();
        $sabores = Config::where('key', 'flavors')->orderBy('order')->pluck('value')->values()->all();

        $datos = [[['key' => '', 'value' => 'Cantidad'], ['key' => '', 'value' => 'Sabor']]];
        foreach ($renglones as $r) {
            $iCantidad = array_search($r['cantidad'], $cantidades, true);
            $iSabor = array_search($r['sabor'], $sabores, true);
            if ($iCantidad === false || $iSabor === false) {
                return [null, 'Cantidad o sabor no válido: ' . $r['cantidad'] . ' / ' . $r['sabor']];
            }
            $datos[] = [
                ['key' => (string) $iCantidad, 'value' => $cantidades[$iCantidad]],
                ['key' => (string) $iSabor, 'value' => $sabores[$iSabor]],
            ];
        }

        if (!empty($piezasProducto)) {
            $suma = self::sumaPiezas($renglones, $piezasProducto);
            if ($suma === null || abs($suma - $piezasProducto) > 0.001) {
                return [null, 'Los sabores suman ' . self::formato((float) $suma) . ' piezas y la orden es de '
                    . $piezasProducto . '. Ajusta las cantidades.'];
            }
        }

        return [json_encode($datos, JSON_UNESCAPED_UNICODE), null];
    }

    private static function formato($numero)
    {
        return rtrim(rtrim(number_format($numero, 1, '.', ''), '0'), '.');
    }
}
