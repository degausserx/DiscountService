<?php

namespace App\Objects\Discounts;

use App\DataModels\Order;
use App\Builders\DiscountBuilder;
use Exception;

class Discount {

    // orders
    private $order;

    // callable
    private $function;
    private $addedFunctions = array();
    private $discountBuilder = null;

    // properties
    private $description;
    private $name;
    private $rewardType;
    private $rewardNumber;
    private $applyRewardTo;
    private $priority;
    private $limit;
    private $each;
    private $filterBy;

    // constructor
    public function __construct($function = null) {
        if (!is_callable($function) && ($function instanceof DiscountBuilder)) {
            $this->function = $function;
        }
        if (is_callable($function) && ($function() instanceof DiscountBuilder)) {
            $this->function = $function();
        }

        $this->processBuilders();

    }

    // add function from derived classes. idk if this is better than making $functions protected
    // this does give me control on making sure the function sent to the object is called last, if at all
    final protected function addDiscount($discount = null) {
        if (!is_callable($discount) && !($discount instanceof DiscountBuilder)) { 
            throw new Exception("Argument mismatch upon object instantiation");
        }
        $this->addedFunctions[] = $discount;
    }

    // get data
    final public function getData() {

        if (!$this->function || !empty($this->addedFunctions)) {
            $this->processBuilders();
        }

        return $this->discountBuilder->getData();
    }

    //handle unwrapping and adding discount builders
    final private function processBuilders() {
        $functions = $this->addedFunctions;
        $discountBuilders = array();
        $functions[] = $this->function;

        // execute functions if provided
        foreach ($functions as $function) {
            $builder = (is_callable($function)) ? $function() : $function;
            if (!($builder instanceof DiscountBuilder)) throw new Exception("not instance of DiscountBuilder");
            $discountBuilders[] = $builder;
        }

        // merge DiscountBuilder requests
        $this->discountBuilder = $this->combineQueries($discountBuilders);

        $this->function = null;
        $this->addedFunctions = array();

        $data = $this->discountBuilder->getData();

        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        if (isset($data['rewardType'])) {
            $this->rewardType = $data['rewardType'];
        }
        if (isset($data['rewardNumber'])) {
            $this->rewardNumber = $data['rewardNumber'];
        }
        if (isset($data['applyRewardTo'])) {
            $this->applyRewardTo = $data['applyRewardTo'];
        }
        if (isset($data['priority'])) {
            $this->priority = $data['priority'];
        }
        if (isset($data['limit'])) {
            $this->limit = $data['limit'];
        }
        if (isset($data['each'])) {
            $this->each = $data['each'];
        }
        if (isset($data['filterBy'])) {
            $this->filterBy = $data['filterBy'];
        }
    }

    // for now we only really expect up to 2 functions to be present, but let's make this future ready
    final private function combineQueries(Array $discountBuilders) {
        $builder = DiscountBuilder::build($discountBuilders[0]);

        for ($x = 0; $x < count($discountBuilders) - 1; $x++) {
            $builder = $builder->build($discountBuilders[$x + 1]);
        }
        return $builder;
    }

    // getters

    public function getDescription() { 
        return $this->description;
     }
    public function getName() { 
        return $this->name;
     }
    public function getRewardType() { 
        return $this->rewardType;
     }
    public function getRewardNumber() { 
        return $this->rewardNumber;
     }
    public function getApplyRewardTo() { 
        return $this->applyRewardTo;
     }
    public function getPriority() { 
        return $this->priority;
     }
    public function getLimit() { 
        return $this->limit;
     }
    public function getEach() { 
        return $this->each;
     }
    public function getFilterBy() { 
        return $this->filterBy;
     }

}