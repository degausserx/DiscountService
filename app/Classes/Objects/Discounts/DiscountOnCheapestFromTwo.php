<?php

namespace App\Objects\Discounts;

use App\DataModels\Order;
use App\Builders\DiscountBuilder;

class DiscountOnCheapestFromTwo extends Discount {

    // be sure to call the parent class, so that functions added below can be customized from the HookLoader
    public function __construct($function = null) {
        parent::__construct($function);

        // you can create child classes to set some default functionality
        // don't worry, all added functions will be applied to DiscountBuilder before the query data of $function
        $this->addFunction(function() { 
            return DiscountBuilder::build()->name('DiscountOnCheapest')->group(5)->applyTo('category|cheapest')->limit(1)->discount(20)->minimum(2)
            ->filterBy('category', '2')->description('If you buy two or more products of category Tools, you get a 20% discount on the cheapest product');
        });

    }

    // you can do stuff with the order before being processed
    protected function begin(Order $order) {
        return $order;
    }

    // you can do stuff with the final order (discounts applied) before being returned
    protected function finalize(Order $order) {
        return $order;
    }
          
}