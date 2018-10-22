<?php

namespace App\Objects\Discounts;

use App\DataModels\Order;
use App\Builders\DiscountBuilder;

class DiscountOnCheapestFromTwo extends Discount {

    // be sure to call the parent class, so that functions added below can be customized from the HookLoader
    // another option is to add ->build($function) to the tail end of your last query
    public function __construct($function = null) {
        parent::__construct($function);

        // you can create child classes to set some default functionality
        // don't worry, all added functions will be applied to DiscountBuilder before the query data of $function
        $this->addDiscount(function() { 
            $discountBuilder = DiscountBuilder::build();
            $discountBuilder->name('DiscountOnCheapest');
            $discountBuilder->description('If you buy two or more products of category Tools, you get a 20% discount on the cheapest product');
            $discountBuilder->group(5);
            $discountBuilder->limit(1);
            //$discountBuilder->each('totalItems', 5);
            return $discountBuilder;
        });

    }
          
}