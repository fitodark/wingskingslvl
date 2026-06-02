<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Dinerstable;
use App\Product;
use App\Venta;
use App\Config;

class SaleDataComposer
{

    public function compose(View $view)
    {
        $dinerstable = Dinerstable::all();
        $drinkProducts = Product::orderBy('name', 'asc')->where([
            ['type', '=', '1'],['active', '=', '1']
        ])->paginate(20);
        $foodProducts = Product::orderBy('name', 'asc')->where([
            ['type', '=', '2'],['active', '=', '1']
        ])->orWhere([
            ['type', '=', '3'],['active', '=', '1']
        ])->paginate(20);

        $drinkProducts->getCollection()->transform(function ($item, $index) use ($drinkProducts) {
            $item->consecutive_id = (($drinkProducts->currentPage() - 1) * $drinkProducts->perPage()) + $index + 1;
            return $item;
        });
        $foodProducts->getCollection()->transform(function ($item, $index) use ($foodProducts) {
            $item->consecutive_id = (($foodProducts->currentPage() - 1) * $foodProducts->perPage()) + $index + 1;
            return $item;
        });

        $piecesList = Config::orderBy('order', 'asc')->where('key', '=',  'pieces')->get();
        $flavorsList = Config::orderBy('order', 'asc')->where('key', '=',  'flavors')->get();

        $view->with('dinerstable', $dinerstable);
        $view->with('drinkProducts', $drinkProducts)
            ->with('i', (request()->input('page', 1) - 1) * 20);

        $view->with('foodProducts', $foodProducts)
            ->with('piecesList', $piecesList)
            ->with('flavorsList', $flavorsList)
            ->with('i', (request()->input('page', 1) - 1) * 20);
    }
}
