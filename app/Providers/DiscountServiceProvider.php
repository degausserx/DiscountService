<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DiscountServiceProvider extends ServiceProvider {

    public function register() {
        $this->app->bind('App\Containers\DiscountContainerInterface', 'App\Containers\DiscountContainer');
    }

}