<?php
namespace App\Traits;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use App\Product;
use App\Venta;
use App\VentasProductos;

trait ComandasDataLibrary {

    // Bebidas = productos de barra; comidas = cocina (alitas y general).
    // Las versiones *Resume dejan fuera los renglones eliminados.

    public function getDrinkData($ventaId) {
        return VentasProductos::where('IdVenta', $ventaId)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', Product::TIPO_BARRA);
            })->get();
    }

    public function getDrinkDataResume($ventaId) {
        return VentasProductos::where('IdVenta', $ventaId)
            ->activos()
            ->whereHas('product', function (Builder $query) {
                $query->where('type', Product::TIPO_BARRA);
            })->get();
    }

    public function getFoodData($ventaId) {
        return VentasProductos::where('IdVenta', $ventaId)
            ->whereHas('product', function (Builder $query) {
                $query->whereIn('type', Product::TIPOS_COCINA);
            })->get();
    }

    public function getFoodDataResume($ventaId) {
        return VentasProductos::where('IdVenta', $ventaId)
            ->activos()
            ->whereHas('product', function (Builder $query) {
                $query->whereIn('type', Product::TIPOS_COCINA);
            })->get();
    }

    /**
     * Productos de la orden actual de la venta ($venta->order), separados
     * en bebidas (barra) y comidas (cocina).
     *
     * @return array [$arrayBebidas, $arrayComidas]
     */
    public function getProductosOrden(\App\Venta $venta) {
        $arrayBebidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->where('order', $venta->order)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', Product::TIPO_BARRA);
            })->get();

        $arrayComidas = VentasProductos::where('IdVenta', $venta->ventaId)
            ->where('order', $venta->order)
            ->whereHas('product', function (Builder $query) {
                $query->whereIn('type', Product::TIPOS_COCINA);
            })->get();

        return [$arrayBebidas, $arrayComidas];
    }

    /**
     * Deja la venta activa en la bandeja después de mandar la orden:
     * aplica el descuento si corresponde y marca los productos como comandados
     * (a partir de aquí eliminarlos requiere PIN).
     */
    public function activarVenta(\App\Venta $venta, $clientId, $apply) {
        $arrayDiscountPercentage = $this->getDiscountPercentage($clientId);
        if (is_array($arrayDiscountPercentage) && count($arrayDiscountPercentage) > 0 && $apply == 1) {
            $discountPercentage = $arrayDiscountPercentage[0]->discountPercentage;
            $venta->montoTotalDescuento = $venta->montoTotal - round(($venta->montoTotal * ($discountPercentage / 100)), 0, PHP_ROUND_HALF_EVEN);
            $venta->apply_discount = TRUE;
        } else {
            $venta->apply_discount = FALSE;
        }

        VentasProductos::where('IdVenta', $venta->ventaId)
            ->where('delete_flag', true)
            ->activos()
            ->update(['delete_flag' => false]);

        $venta->activo = 1;
        $venta->save();
    }

    public function getDiscountPercentage($clientId) {
        DB::connection()->enableQueryLog();

        $results = DB::select( DB::raw("
            SELECT max(v.ventaId) as ventaId
            , count(v.ventaId) as total
            , sum(v.montoTotal) as montoTotal
            , v.client_id as clientId
            , max(date_format(v.created_at, '%Y-%m-%d')) as date
            , c.name
            , (select c.value from configs c where c.key = 'discountPercentage') as discountPercentage
            FROM ventas v
            left join clients c on c.id = v.client_id
            left join promotions_clients pc on pc.client_id = v.client_id and pc.estatus = 1
            where date_format(v.created_at, '%Y-%m') = :date and v.activo = 1 and v.estatus = 2 
            and v.client_id in (:clientId) and (v.ventaId > pc.IdVenta or pc.IdVenta is null)
            group by v.client_id, c.name
            HAVING count(v.ventaId) >= (select c.value from configs c where c.key = 'salesNumber' and c.value > 0)
            order by v.created_at desc"), array('clientId' => $clientId, 'date' => date('Y-m')));
        $queries = DB::getQueryLog();
        return $results;
    }

    public function getClientSalesDetails($clientId) {
        DB::connection()->enableQueryLog();

        $results = DB::select( DB::raw("
            select v.ventaId, v.montoTotal, v.client_id, date_format(v.created_at, '%Y-%m-%d %H:%i') as date, c.name 
            from ventas v
            left join clients c on c.id = v.client_id
            left join promotions_clients pc on pc.client_id = v.client_id -- and v.ventaId = pc.IdVenta
            where date_format(v.created_at, '%Y-%m') = :date and v.activo = 1 and v.estatus = 2 and (pc.estatus = 1 or pc.estatus is null)
            and v.client_id in (:clientId) and (v.ventaId > pc.IdVenta or pc.IdVenta is null)
            order by v.created_at desc"), array('clientId' => $clientId, 'date' => date('Y-m')));
        $queries = DB::getQueryLog();
        return $results;
    }

    public function getMontoTotalVenta($ventaId) {
        $montoTotal = DB::table('ventasproductos')
                    ->select(DB::raw('sum(montoVenta) as montoVenta, sum(cantidad) as cantidad'))
                    ->where('idVenta', '=', $ventaId)
                    ->where('estatus', VentasProductos::ESTATUS_ACTIVO)
                    ->groupBy('idVenta')
                    ->get();
        return $montoTotal;
    }

    public function getTotalVentaByClientId($clientId, $date) {
        DB::connection()->enableQueryLog();

        $resumeTotalSales = DB::table('ventas')
                    ->select(DB::raw("max(ventaId) as ventaId, count(ventaId) as totalVentas
                    , sum(montoTotal) as montoTotal
                    , client_id, max(date_format(created_at, '%Y-%m-%d %H:%i')) as date"))
                    ->where('client_id', $clientId)
                    ->where('estatus', Venta::ESTATUS_CERRADA)
                    ->where('activo', 1)
                    ->where(
                        DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), '=', $date
                    )->groupBy('client_id')
                    ->get();
        $queries = DB::getQueryLog();
        //dd($queries);
        return $resumeTotalSales;
    }

    public function getVentaByClientAndDate($clientId, $date) {
        DB::connection()->enableQueryLog();
        Log::info('getVentaByClientAndDate: '.$clientId);
        $resumeTotalSales = DB::table('ventas')
                    ->select(DB::raw("ventaId, montoTotal
                    , client_id, date_format(created_at, '%Y-%m-%d %H:%i') as date"))
                    ->where('client_id', $clientId)
                    ->where('estatus', Venta::ESTATUS_CERRADA)
                    ->where('activo', 1)
                    ->where(
                        DB::raw("DATE_FORMAT(created_at,'%Y-%m')"), '=', $date
                    )->get();
        $queries = DB::getQueryLog();
        //dd($queries);
        return $resumeTotalSales;
    }

    public function getPromotionsClients($ventaId, $date) {
        DB::connection()->enableQueryLog();

        $montoTotal = DB::table('promotions_clients')
                    ->where([
                        ['idVenta', '=', $ventaId]
                    ])->where(
                        DB::raw("DATE_FORMAT(created_at,'%Y-%m-%d')"), '=', $date
                    )->get();
        $queries = DB::getQueryLog();
        //dd($queries);
        //Log::info('getPromotionsClients: '.print_r($queries));
        return $montoTotal;
    }

    public function getInventarioProductosDetails() {
        DB::connection()->enableQueryLog();

        $results = DB::select( DB::raw("
            select p.id, p.name  
            from inventario_productos ip 
            left join products p on p.id = ip.idProducto
            where p.active is true
            union 
            select p.id, p.name
            from products p 
            where p.active is true and p.type = :insumo"), ['insumo' => Product::TIPO_INSUMO]);
        $queries = DB::getQueryLog();
        //dd($queries);
        //Log::info('getPromotionsClients: '.print_r($queries));
        return $results;
    }

    public function getUpdateMontosInventario($idCompra) {
        DB::connection()->enableQueryLog();

        $results = DB::select( DB::raw("
            update inventario_productos inupdate
            inner join compras_productos cp on inupdate.idProducto = cp.idProducto 
            inner join compras c on c.idCompra = cp.idCompra 
            set inupdate.cantidad = inupdate.cantidad + cp.cantidad
            where cp.idCompra = :idCompra and c.status = 0"), array('idCompra' => $idCompra));
        $queries = DB::getQueryLog();
        return $results;
    }

        public function getUpdateMontosInventarioFromVentas($idVenta) {
        DB::connection()->enableQueryLog();

        // Se agrupa por producto porque MySQL actualiza cada fila de inventario una sola vez
        // por UPDATE con join; sin agrupar, dos renglones del mismo producto solo restaban uno.
        // vp.estatus = 1 excluye los productos eliminados de la venta.
        $results = DB::update("
            update inventario_productos ip
            inner join (
                select vp.idProducto, sum(vp.cantidad) as cantidad
                from ventasproductos vp
                inner join ventas v on v.ventaId = vp.IdVenta
                where v.ventaId = :idVenta and v.estatus = 2 and vp.estatus = 1
                group by vp.idProducto
            ) vendidos on vendidos.idProducto = ip.idProducto
            set ip.cantidad = ip.cantidad - vendidos.cantidad", array('idVenta' => $idVenta));
        $queries = DB::getQueryLog();
        return $results;
    }

    public function getTotalVenta($idCorte, $paymentType) {
        DB::connection()->enableQueryLog();

        $results = DB::select( DB::raw("
            select max(cm.idCorteMovimiento) as idCorteMovimiento, 
                count(cm.idCorteMovimiento) as totalVentas,
                sum(cm.monto) as montoTotalVentas
            from corte_caja_movimientos cm
            inner join ventas v on v.ventaId = cm.idVenta
            where cm.idCorte = :idCorte and v.payment_type = :paymentType
            group by cm.idCorte"), array('idCorte' => $idCorte, 'paymentType' => $paymentType));
        $queries = DB::getQueryLog();
        //dd($queries);
        return $results;
    }

    public function getTotalCompras($idCorte) {
        DB::connection()->enableQueryLog();

        $resumeTotalSales = DB::table('corte_caja_movimientos')
                    ->select(DB::raw("max(idCorteMovimiento) as idCorteMovimiento, count(idCorteMovimiento) as totalCompras
                    , sum(monto) as montoTotalCompras"))
                    ->where('idCorte', $idCorte)
                    ->whereIn('idTipo', [\App\CorteCajaMovimientos::TIPO_COMPRA, \App\CorteCajaMovimientos::TIPO_COMPRA_LIBRE])
                    ->groupBy('idCorte')
                    ->get();
        $queries = DB::getQueryLog();
        //dd($queries);
        return $resumeTotalSales;
    }
}
