<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Dinerstable;
use App\Product;
use App\Venta;

class SaleDataComposer
{

    public function compose(View $view)
    {
        $dinerstable = Dinerstable::all();
        $drinkProducts = Product::where('type', 1)->get();
        $foodProducts = Product::where('type', 2)->orWhere('type', 3)->get();

        $view->with('dinerstable', $dinerstable);
        $view->with('drinkProducts', $drinkProducts);
        $view->with('foodProducts', $foodProducts);
    }
}
