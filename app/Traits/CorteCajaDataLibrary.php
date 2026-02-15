<?php
namespace App\Traits;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use App\CorteCaja;

trait CorteCajaDataLibrary {

    public function getCurrentCorteCaja($date) {
        if ($date != null){
            $cortecajaCurrent = CorteCaja::where([
                ['fechaApertura', '=', $date],['corteStatus', '=', false]
            ])->get();
        } else {
            $cortecajaCurrent = CorteCaja::where('corteStatus', true)->get();
        }
        return $cortecajaCurrent;
    }

    public function getUpdateMontoTotalCorte($idCorte) {
        DB::connection()->enableQueryLog();

        $results = DB::select( DB::raw("
            update corte_caja cc
            join ( 
                select idCorte, sum(montoVentas) - sum(montoCompras) as montoTotal 
                from (
                    select cc.idCorte, 
                    case ccm.idTipo when 1 then ccm.monto else 0 end montoVentas,
                    case ccm.idTipo when 2 then ccm.monto when 3 then ccm.monto else 0 end montoCompras
                    from corte_caja cc
                    left join corte_caja_movimientos ccm on ccm.idCorte = cc.idCorte
                ) as ccm group by ccm.idCorte
            ) mov on mov.idCorte = cc.idCorte set cc.montoCierre = cc.montoApertura + mov.montoTotal
            where cc.idCorte = :idCorte "), array('idCorte' => $idCorte));
        $queries = DB::getQueryLog();
        return $results;
    }

}
