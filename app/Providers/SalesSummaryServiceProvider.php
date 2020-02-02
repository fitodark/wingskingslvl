<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class SalesSummaryServiceProvider extends ServiceProvider
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
        // View::composer('puntoventa.salessummary.index',
        //    'App\Http\ViewComposers\SalesSummaryComposer');
    }
}
