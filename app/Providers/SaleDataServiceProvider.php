<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class SaleDataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // View::share('arrayBebidas', []);

        View::composer(['puntoventa.comandas.tableSelection',
            'puntoventa.comandas.clientSelection',
            'puntoventa.comandas.drinksTab',
            'puntoventa.comandas.foodsTab'],
            'App\Http\ViewComposers\SaleDataComposer');
    }
}
