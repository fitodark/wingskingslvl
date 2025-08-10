<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Config;

class DiscountDataComposer {

    public function compose(View $view) {
        $discountPercentageObject = Config::where('key', '=', 'discountPercentage')->first();

        $view->with('discountPercentageObject', $discountPercentageObject);
    }
}
