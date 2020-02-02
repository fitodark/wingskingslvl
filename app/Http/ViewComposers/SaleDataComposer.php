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
        $drinkProducts = Product::where('type', 1)->paginate(10);
        $foodProducts = Product::orderBy('id', 'desc')->where('type', 2)->orWhere('type', 3)->paginate(10);

        $piecesList = Config::orderBy('order', 'asc')->where('key', '=',  'pieces')->get();
        $flavorsList = Config::orderBy('order', 'asc')->where('key', '=',  'flavors')->get();

        $view->with('dinerstable', $dinerstable);
        $view->with('drinkProducts', $drinkProducts)
            ->with('i', (request()->input('page', 1) - 1) * 10);

        $view->with('foodProducts', $foodProducts)
            ->with('piecesList', $piecesList)
            ->with('flavorsList', $flavorsList)
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }
}
