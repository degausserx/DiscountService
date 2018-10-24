<?php

namespace App\HookLoaders;

use App\Builders\DiscountBuilder;
use App\Objects\Discounts\DiscountOnCheapestFromTwo;
use App\Objects\Discounts\Discount;

// Decided to go about doing it this way, because I'm thinking it might be better for scalability than having to go deeper into code somewhere else.
// if more than one product matches the filter, then the first found product under the filter is used as the "give" if applied to category, unless
// it's used together with cheapest, like: 'applyTo' =? 'category|cheapest'

    //      'description' => '',                    A description of the discount
    //      'name' => '',                           Name of the Discount
    //      'rewardType' => '',                     options are: discount (N%) or item (N items)
    //      'rewardNumber' =>                       uses the N from above
    //      'applyRewardTo' => '',                  options are: order, productLine, cheapestItem, cheapestProduct
    //                                              "Reward types" Item always applies to a productLine
    //      'limit' => '',                          default is 1. limit the number of times the reward may be applied. 0 is infinite
    //      'each'
    //          'totalSpent' => '',                 if N or more has been spent in total. default is 0 for disabled
    //          'totalItems' => '',                 every N or more filtered items. default is 0 for disabled
    //      'filterBy' =>
    //          'product' =>
    //              'id' => '',                         separated by | // filter by product
    //              'price' => ''
    //                  'moreThan' => '',               filter by equality: more than N // price per item
    //                  'lessThan' => '',               filter by equality: less than N
    //                  'equals' => '',                 filter by equality: equal to N
    //              'itemSum' => ''
    //                  'moreThan' => '',               filter by equality: more than N // total items per id
    //                  'lessThan' => '',               filter by equality: less than N // 
    //                  'equals' => '',                 filter by equality: equal to N  //
    //              'productSum' => ''
    //                  'moreThan' => '',               filter by equality: more than N // total items per id
    //                  'lessThan' => '',               filter by equality: less than N // 
    //                  'equals' => '',                 filter by equality: equal to N  //
    //          'category' =>
    //              'id' => '',                         separated by | // filter by group
    //          'order'
    //              'price' => ''
    //                  'moreThan' => '',               filter by equality: more than N
    //                  'lessThan' => '',               filter by equality: less than N
    //                  'equals' => '',                 filter by equality: equal to N
    //              'itemSum' => ''
    //                  'moreThan' => '',               filter by equality: more than N
    //                  'lessThan' => '',               filter by equality: less than N
    //                  'equals' => '',                 filter by equality: equal to N
    //          'lifetimeSpend'                  
    //              'moreThan' => '',                   filter by item lifetime spend: more than N
    //              'lessThan' => '',                   filter by item lifetime spend: less than N
    //              'equals' => '',                     filter by item lifetime spend: equal to N 

class DiscountHookLoader {

    // the load method, where DiscountContainer is pointed towards from the controller.
    public function load() {
        return array(


            // METHOD 1: the query builder method!
            // A 10% discount has been applied to your full order because you have spent over €1000
            new Discount(DiscountBuilder::build()
                ->name('TotalSpent')
                ->rewardType('discount')
                ->rewardNumber(10)
                ->applyRewardTo('order')
                ->filterBy('lifetimeSpend.moreThanEqual', 1000)
                ->description('A 10% discount has been applied to your full order because you have spent over €1000')
            ),

            // METHOD 2: the callable method!!
            // For every product of category Switches, when you buy five, you get a sixth for free
            
            new Discount(function() {
                $object = DiscountBuilder::build();
                $object->rewardType('item');
                $object->rewardNumber(1);
                $object->applyRewardTo('productLine');
                $object->filterBy('category.id', 2);
                $object->limit(0);
                $object->each('totalItems', 5);
                return $object->name('Buy51Free')->description('For every product of category Switches, when you buy five, you get a sixth for free');
            }),


            // METHOD 3: the extended class method!!!
            // Here we're using an instance of "DiscountOnCheapestFromTwo", and applying our own data on top of built in functionality
            // If you buy two or more products of category Tools, you get a 20% discount on the cheapest product
            new DiscountOnCheapestFromTwo(DiscountBuilder::build()->name('newNameForDiscount')),

/*
            // METHOD 4: the merge method!!!!
            // object functionality allows one instance to take the values of another.
            // This happens because the DiscountBuilder class uses composition, essentially feeding off anything you throw at it
            // we can combine this with the build() method to create and inject any needed objects
            // only values which have been set at least once, are passed on

           new Discount(function() {
                $filter = DiscountBuilder::build();
                $filter->filterBy('order.price.moreThanEqual', 1000);
                $filter->filterBy('category.id', 7);
                $filter->rewardType('discount');
                $filter->rewardNumber('5');
                $filter->applyRewardTo('order');
                $filter->limit(5);

                $criteria = DiscountBuilder::build();
                $criteria->limit(1);

                // you can inject more builders at any point:
                return DiscountBuilder::build($filter)->name('Some name')->build($criteria)->name('new name')
                ->description('RANDOM CAPS BASED CATEGORY');

            }),

            // METHOD 5: the ultimate method?????
            // a combination of 1 and 4, allowing you to create more DiscountBuilders available from class methods or arrays (shared amongst the class),
            // then combining what is needed with some build() calls.
            // I think method 3 can also be powerful
            */
        );

    }

}