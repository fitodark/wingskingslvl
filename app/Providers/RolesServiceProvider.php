<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class RolesServiceProvider extends ServiceProvider
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
        View::composer([
            'config.users.index',
            'config.users.create',
            'config.users.edit',
            'config.users.bodyForm'
            ],
           'App\Http\ViewComposers\RolesComposer');
    }
}
