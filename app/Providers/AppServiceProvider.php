<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Contracts\Repositories\CustomerRepositoryContract', 'App\Repositories\CustomerRepository');
        $this->app->bind('App\Contracts\Repositories\ProductRepositoryContract', 'App\Repositories\ProductRepository');
        $this->app->bind('App\Contracts\DiscountServiceContract', 'App\Services\DiscountService');
        $this->app->bind('App\Contracts\DiscountServiceContainerContract', 'App\Containers\DiscountServiceContainer');
    }
}
