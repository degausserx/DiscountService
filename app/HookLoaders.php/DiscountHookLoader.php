<?php

namespace App\HookLoaders;

use App\Builders\DiscountBuilder;
use App\Objects\Discounts\DiscountOnCheapestFromTwo;
use App\Objects\Discounts\Discount;

// Decided to go about doing it this way, because I'm thinking it might be better for scalability than having to go deeper into code somewhere else.
// if more than one product matches the filter, then the first found product under the filter is used as the "give" if applied to category, unless
// it's used together with cheapest, like: 'applyTo' =? 'category|cheapest'

//      'group' => '',              Default is 0. Only 1 discount from any given group is eligable to apply a discount to the order. 
//                                  Discounts are processed according to groupId from lowest to highest
//      'description' => '',        A descriotion of the discount
//      'reduction' => '',          specifically, N is taken away from the price
//      'discount' => '',           specifically, N% is taken away from the price
//      'give' => '',               add N items to order
//      'applyTo' => '',            product, category, order, cheapest, dearest. Multiple values can be used with the | separator
//      'totalSpent' => '1000',     if N or more has been spent in total
//      'totalItems' => '',         every N items meets the criteria for an offer
//      'limit' => '',              default is 1. limit the number of times 'totalItems' and 'totalSpent' are used. 0 for infinite
//      'product' => '',            filter by product IDs. you can specify multiple ids using the | separator
//      'category' => '',           category: filter by category IDs. you can specify multiple ids using the | separator
//      'moreThan' => '',           filter by price: more than N
//      'lessThan' => '',           filter by price: less than N
//      'moreThanEqual' => '',      filter by price: more than or equal to N
//      'lessThanEqual' => '',      filter by price: less than or equal to N
//      'equals' => '',             filter by price: equal to N
//      'disabled' => ''            default is false, set to true to disable the chain discount query

class DiscountHookLoader {

    // the load method, where DiscountContainer is pointed towards from the controller.
    public function load() {
        return array(


            // METHOD 1: the query builder method!
            // A 10% discount has been applied to your full order because you have spent over €1000
            new Discount(DiscountBuilder::build()
                ->name('TotalSpent')
                ->group(0)
                ->totalSpent(1000)
                ->applyTo('order')
                ->limit(1)
                ->discount(10)
                ->description('A 10% discount has been applied to your full order because you have spent over €1000')
            ),

            // METHOD 2: the callable method!!!
            // For every product of category Switches, when you buy five, you get a sixth for free
            new Discount(function() {
                $object = DiscountBuilder::build();
                $object->group(5);
                $object->applyTo('product');
                $object->limit(0);
                $object->filterBy('category', '2');
                $object->totalItems(5);
                $object->give(1);
                return $object->name('Buy51Free')->description('For every product of category Switches, when you buy five, you get a sixth for free');
            }),


            // METHOD 3: the extended class method!!
            // Here we're using an instance of "DiscountOnCheapestFromTwo", and applying our own data on top of built in functionality
            // If you buy two or more products of category Tools, you get a 20% discount on the cheapest product
            new DiscountOnCheapestFromTwo(DiscountBuilder::build()->name('newNameForDiscount')),


            // METHOD 4: the merge method!!
            // object functionality allows one instance to take the values of another.
            // This happens because the DiscountBuilder class uses composition, essentially feeding off anything you throw at it
            // we can combine this with the build() method to create and inject any needed objects
            // only values which have been set at least once, are passed on

           new Discount(function() {
                $filter = DiscountBuilder::build();
                $filter->group(10);
                $filter->filterBy('category', '1|2');
                $filter->filterBy('moreThanEqual', '100');
                $filter->applyTo('product');
                $filter->limit(1);

                $criteria = DiscountBuilder::build(); // <-- you can enter the $filter variable directly, or merge during method chaining
                $criteria->reduction(5);
                $criteria->totalSpent(100);
                $criteria->limit(0); // overwrite. using 0 limit with totalSpent means 'for every totalSpent'
                $criteria->applyTo('category'); // overwrite

                // you can inject more builders at any point:
                return DiscountBuilder::build($filter)->name('Some name')->build($criteria)
                ->description('For every product of category Switches, when you buy five, you get a sixth for free');

            }),

            // METHOD 5: the ultimate method?
            // a combination of 1 and 4, allowing you to create more DiscountBuilders available from class methods or arrays (shared amongst the class),
            // then combining what is needed with some build() calls.
            // I think method 3 can also be powerful
        );
    }

}