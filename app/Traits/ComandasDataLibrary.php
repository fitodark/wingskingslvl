<?php
namespace App\Traits;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use App\VentasProductos;

trait ComandasDataLibrary {

    public function getDrinkData($ventaId) {
        $arrayBebidas = VentasProductos::where('IdVenta', $ventaId)
              ->whereHas('product', function (Builder $query) {
                  $query->where('type', '=', '1');
              })->get();
        return $arrayBebidas;
    }

    public function getDrinkDataResume($ventaId) {
        $arrayBebidas = VentasProductos::where('IdVenta', $ventaId)
              ->whereHas('product', function (Builder $query) {
                  $query->where('type', '=', '1')->where([
                        ['estatus', '=', 1]
                    ]);
              })->get();
        return $arrayBebidas;
    }

    public function getFoodData($ventaId) {
        $arrayComidas = VentasProductos::where('IdVenta', $ventaId)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', 2)->orWhere('type', '=', 3);
            })->get();
        return $arrayComidas;
    }

    public function getFoodDataResume($ventaId) {
        $arrayComidas = VentasProductos::where('IdVenta', $ventaId)
            ->whereHas('product', function (Builder $query) {
                $query->where('type', '=', 2)->where([
                        ['estatus', '=', 1]
                    ])->orWhere('type', '=', 3)->where([
                        ['estatus', '=', 1]
                    ]);
            })->get();
        return $arrayComidas;
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
                    ->where([
                        ['estatus', '=', 1]
                    ])
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
                    ->where([
                        ['client_id', '=', $clientId]
                    ])->where([
                        ['estatus', '=', 2]
                    ])->where([
                        ['activo', '=', 1]
                    ])->where(
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
                    ->where([
                        ['client_id', '=', $clientId]
                    ])->where([
                        ['estatus', '=', 2]
                    ])->where([
                        ['activo', '=', 1]
                    ])->where(
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
            where p.active is true and p.type = 4"));
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

        $results = DB::select( DB::raw("
            update inventario_productos ip
            inner join ventasproductos vp on vp.idProducto = ip.idProducto 
            inner join ventas v on v.ventaId = vp.IdVenta 
            set ip.cantidad = ip.cantidad - vp.cantidad
            where v.ventaId = :idVenta and v.estatus = 2"), array('idVenta' => $idVenta));
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
                    ->where([
                        ['idCorte', '=', $idCorte]
                    ])->where([
                        ['idTipo', '=', 2]
                    ])->orWhere([
                        ['idCorte', '=', $idCorte]
                    ])->where([
                        ['idTipo', '=', 3]
                    ])->groupBy('idCorte')
                    ->get();
        $queries = DB::getQueryLog();
        //dd($queries);
        return $resumeTotalSales;
    }
}
