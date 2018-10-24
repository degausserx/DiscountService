<?php

namespace App\Objects\Discounts;

use App\DataModels\Order;
use App\Builders\DiscountBuilder;

class DiscountOnCheapestFromTwo extends Discount {

    // be sure to call the parent class last, so that built in functionality is placed first
    public function __construct($function = null) {

        // you can create child classes to set some default functionality
        // don't worry, all added functions will be applied to DiscountBuilder before the query data of $function
        $this->addDiscount(function() { 
            $discountBuilder = DiscountBuilder::build();
            $discountBuilder->name('DiscountOnCheapest');
            $discountBuilder->rewardType('discount');
            $discountBuilder->rewardNumber(20);
            $discountBuilder->applyRewardTo('cheapestProduct');
            $discountBuilder->filterBy('category.id', 1);
            $discountBuilder->filterBy('product.productSum.moreThan', 1);
            $discountBuilder->description('If you buy two or more products of category Tools, you get a 20% discount on the cheapest product');
            //$discountBuilder->each('totalItems', 5);
            return $discountBuilder;
        });

        parent::__construct($function);

    }
          
}