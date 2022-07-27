<?php

namespace Rutatiina\Inventory;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        //include __DIR__.'/routes/routes.php';
        //include __DIR__.'/routes/api.php';

        // $this->loadViewsFrom(__DIR__.'/resources/views', 'inventory');
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //$this->app->make('Rutatiina\Inventory\Http\Controllers\InventoryController');
    }
}
