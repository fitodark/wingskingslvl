<?php

namespace App\Services;

use App\Venta;
use Illuminate\Support\Facades\DB;

/**
 * Bandeja de comandas (ventas abiertas) y su refresco automático.
 *
 * El agente de WhatsApp inserta ventas directo en MySQL, así que Laravel no se entera.
 * La pantalla pregunta cada pocos segundos por la "firma" de la bandeja; si cambió,
 * vuelve a pintar la tabla. Ver docs/plan-bandeja-tiempo-real.md.
 */
class BandejaService
{
    const POR_PAGINA = 10;

    /** Ventas abiertas, de la más nueva a la más vieja, paginadas con enlaces a /comandas. */
    public function ventas()
    {
        return Venta::orderBy('ventaId', 'desc')
            ->where('activo', 1)
            ->where('estatus', Venta::ESTATUS_ABIERTA)
            ->paginate(self::POR_PAGINA)
            ->withPath(route('comandas'));
    }

    /**
     * Resumen de las ventas abiertas y sus productos. Cambia cuando se abre, cobra o cancela
     * una venta, o cuando se agregan, editan o eliminan productos (por el POS o por SQL directo).
     */
    public function firma()
    {
        $ventas = DB::table('ventas')
            ->where('activo', 1)
            ->where('estatus', Venta::ESTATUS_ABIERTA)
            ->selectRaw('COUNT(*) AS c, MAX(ventaId) AS m, MAX(updated_at) AS u, SUM(montoTotal) AS t')
            ->first();

        $productos = DB::table('ventasproductos AS vp')
            ->join('ventas AS v', 'v.ventaId', '=', 'vp.IdVenta')
            ->where('v.activo', 1)
            ->where('v.estatus', Venta::ESTATUS_ABIERTA)
            ->selectRaw('COUNT(*) AS c, MAX(vp.updated_at) AS u, SUM(vp.cantidad) AS q, SUM(vp.estatus) AS e, SUM(vp.delete_flag) AS d')
            ->first();

        return md5(json_encode([$ventas, $productos]));
    }

    /** Folio más alto que existe; la pantalla lo usa para saber qué ventas son nuevas. */
    public function maxFolio()
    {
        return (int) DB::table('ventas')->max('ventaId');
    }

    /** Ventas abiertas del agente con folio en (desde, hasta]: las que hay que avisar. */
    public function nuevasDelAgente($desde, $hasta)
    {
        return Venta::with('client')
            ->where('venta_agente', 1)
            ->where('activo', 1)
            ->where('estatus', Venta::ESTATUS_ABIERTA)
            ->where('ventaId', '>', (int) $desde)
            ->where('ventaId', '<=', (int) $hasta)
            ->orderBy('ventaId')
            ->get()
            ->map(function (Venta $venta) {
                return [
                    'folio' => $venta->ventaId,
                    'cliente' => optional($venta->client)->name,
                    'tipo' => Venta::TIPOS[$venta->type] ?? '',
                    'express' => $venta->esEnvioExpress(),
                ];
            })
            ->values()
            ->all();
    }
}
