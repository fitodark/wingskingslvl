<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class SalesServiceProvider extends ServiceProvider
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
        View::composer('puntoventa.comandas.index',
           'App\Http\ViewComposers\SalesComposer');
        
        View::composer('puntoventa.comandas.index',
           'App\Http\ViewComposers\DiscountDataComposer');
        
        View::composer('puntoventa.comandas.index',
           'App\Http\ViewComposers\CorteCajaComposer');

        View::composer('cortecajamovimientos.index',
           'App\Http\ViewComposers\CorteCajaComposer');
    }
}
