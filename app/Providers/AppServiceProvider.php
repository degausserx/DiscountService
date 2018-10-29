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

        $this->app->singleton('App\Repositories\CustomerRepository', function ($app) {
            return new \App\Repositories\CustomerRepository();
        });

        $this->app->singleton('App\Repositories\ProductRepository', function ($app) {
            return new \App\Repositories\ProductRepository();
        });

        $this->app->singleton('App\Repositories\OrderRepository', function ($app) {
            return new \App\Repositories\OrderRepository();
        });

        $this->app->bind('App\Contracts\Repositories\CustomerRepositoryContract', 'App\Repositories\CustomerRepository');
        $this->app->bind('App\Contracts\Repositories\ProductRepositoryContract', 'App\Repositories\ProductRepository');
        $this->app->bind('App\Contracts\Repositories\OrderRepositoryContract', 'App\Repositories\OrderRepository');
        $this->app->bind('App\Contracts\DiscountServiceContainerContract', 'App\Containers\DiscountServiceContainer');
    }
}
