<?php

namespace App\Http\ViewComposers;

use DB;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use App\Config;

class ConfigComposer {

    public function compose(View $view) {
        DB::connection()->enableQueryLog();
        $productsType = DB::table('configs as c')
                    ->where('key', 'products_type')
                    ->orderBy('order', 'asc')
                    ->pluck('value', 'order')
                    ->toArray();
        $productsPromotionType = DB::table('configs as c')
                    ->where('key', 'products_promotion_type')
                    ->orderBy('order', 'asc')
                    ->pluck('value', 'order')
                    ->toArray();
        $queries = DB::getQueryLog();
        $view->with('productsType', $productsType)
            ->with('productsPromotionType', $productsPromotionType
        );
    }
}
