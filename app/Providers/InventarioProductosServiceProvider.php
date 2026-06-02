<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class InventarioProductosServiceProvider extends ServiceProvider
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
        View::composer(['inventarioproductos.index'],
            'App\Http\ViewComposers\InventarioProductosComposer');
    }
}
