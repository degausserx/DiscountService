<?php

namespace App\Objects\Discounts;

use App\DataModels\Order;
use App\Builders\DiscountBuilder;
use Exception;

class Discount {

    // orders
    private $order;

    // callable
    private $function = array();
    private $addedFunctions = array();
    private $discountBuilder = null;

    // constructor
    public function __construct($function = null) {
        if (!is_callable($function) && !($function instanceof DiscountBuilder)) {
            throw new Exception("Discount:: instantiation requires a DiscountBuilder or a function");
        }
        $this->function = $function;
    }

    // add function from derived classes. idk if this is better than making $functions protected
    // this does give me control on making sure the function sent to the object is called last, if at all
    final protected function addDiscount($discount = null) {
        if (!is_callable($discount) && !($discount instanceof DiscountBuilder)) { 
            throw new Exception("Argument mismatch upon object instantiation");
        }
        $this->addedFunctions[] = $discount;
    }

    // handle unwrapping the queries
    final public function getData() {
        // get core functionality, add inline functionality

        if (!empty($this->function) || !empty($this->addedFunctions)) {
            $functions = $this->addedFunctions;
            $discountBuilders = array();
            array_push($functions, $this->function);

            // execute functions if provided
            foreach ($functions as $function) {
                $builder = (is_callable($function)) ? $function() : $function; 
                if (!($builder instanceof DiscountBuilder)) throw new Exception("Failed to get ObjectBuilder from supplied callable");
                $discountBuilders[] = $builder;
            }

            // merge DiscountBuilder requests
            $this->discountBuilder = $this->combineQueries($discountBuilders);

            $this->function = array();
            $this->addedFunctions = array();
        }

        return $this->discountBuilder->getData();
    }

    // for now we only really expect up to 2 functions to be present, but let's make this future ready
    final private function combineQueries(Array $discountBuilders) {
        $builder = DiscountBuilder::build($discountBuilders[0]);

        for ($x = 0; $x < count($discountBuilders) - 1; $x++) {
            $builder = $builder->build($discountBuilders[$x + 1]);
        }
        return $builder;
    }
          
}