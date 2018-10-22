<?php

namespace App\HookLoaders;

use App\Builders\DiscountBuilder;
use App\Objects\Discounts\DiscountOnCheapestFromTwo;
use App\Objects\Discounts\Discount;

// Decided to go about doing it this way, because I'm thinking it might be better for scalability than having to go deeper into code somewhere else.
// if more than one product matches the filter, then the first found product under the filter is used as the "give" if applied to category, unless
// it's used together with cheapest, like: 'applyTo' =? 'category|cheapest'

    //      'group' => '',                          Default is 0. Only 1 discount from any given group is eligable to apply a discount to the order
    //                                              Discounts are processed according to groupId from lowest to highest
    //      'description' => '',                    A description of the discount
    //      'name' => '',                           Name of the Discount
    //      'rewardType' => '',                     options are: reduction (N), discount (N%) or give (N items)
    //      'rewardNumber' =>                       uses the N from above
    //      'applyRewardTo' => '',                  options are: order, singleItem, singleItemQuantity
    //                                              Reductions always apply to the order. Give always applies to a singleItemQuantity
    //      'priority' => ''                        options are: cheapest, dearest <-- default cheapest
    //      'limit' => '',                          default is 1. limit the number of times the reward may be applied. 0 is infinite
    //      'each'
    //          'totalSpent' => '',                 if N or more has been spent in total. default is 0 for disabled
    //          'totalItems' => '',                 every N or more items have been purchased in total. default is 0 for disabled
    //          'applyTo' => '',                    options are: order, singleItem, singleItemQuantity, category
    //      'filterBy' =>
    //          'product' =>
    //              'id' => '',                         separated by |
    //              'price' => ''
    //                  'moreThan' => '',               filter by equality: more than N
    //                  'lessThan' => '',               filter by equality: less than N
    //                  'equals' => '',                 filter by equality: equal to N
    //              'itemSum' => ''
    //                  'moreThan' => '',               filter by equality: more than N
    //                  'lessThan' => '',               filter by equality: less than N
    //                  'equals' => '',                 filter by equality: equal to N
    //          'category' =>
    //              'id' => '',                         separated by |
    //              'price' => ''
    //                  'moreThan' => '',               filter by equality: more than N
    //                  'lessThan' => '',               filter by equality: less than N
    //                  'equals' => '',                 filter by equality: equal to N
    //              'itemSum' => ''
    //                  'moreThan' => '',               filter by equality: more than N
    //                  'lessThan' => '',               filter by equality: less than N
    //                  'equals' => '',                 filter by equality: equal to N
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
    //      'enabled' => ''                             default is true, set to false to disable Discount

class DiscountHookLoader {

    // the load method, where DiscountContainer is pointed towards from the controller.
    public function load() {
        return array(


            // METHOD 1: the query builder method!
            // A 10% discount has been applied to your full order because you have spent over €1000
            new Discount(DiscountBuilder::build()
                ->name('TotalSpent')
                ->group(0)
                ->rewardType('discount')
                ->rewardNumber('10')
                ->applyRewardTo('order')
                ->filterBy('lifetimeSpend.moreThanEquals', 1000)
                ->limit(1)
                ->description('A 10% discount has been applied to your full order because you have spent over €1000')
            ),

            // METHOD 2: the callable method!!
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


            // METHOD 3: the extended class method!!!
            // Here we're using an instance of "DiscountOnCheapestFromTwo", and applying our own data on top of built in functionality
            // If you buy two or more products of category Tools, you get a 20% discount on the cheapest product
            new DiscountOnCheapestFromTwo(DiscountBuilder::build()->name('newNameForDiscount')),


            // METHOD 4: the merge method!!!!
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

            // METHOD 5: the ultimate method?????
            // a combination of 1 and 4, allowing you to create more DiscountBuilders available from class methods or arrays (shared amongst the class),
            // then combining what is needed with some build() calls.
            // I think method 3 can also be powerful
        );
    }

}