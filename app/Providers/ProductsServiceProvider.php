<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class ProductsServiceProvider extends ServiceProvider
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

        View::composer(['puntoventa.productos.create',
            'puntoventa.productos.edit',
            'puntoventa.productos.index'],
            'App\Http\ViewComposers\ConfigComposer');
    }
}
