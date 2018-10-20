<?php

namespace App\Builders;

// could have gone more dynamic with properties here, but sometimes visualization is good.
// as a sacrifice, property and method names should match to reduce complexity
class DiscountBuilder {

    private $group = 0;
    private $name = '';
    private $description = '';
    private $filterBy = array();
    private $disabled = 0;
    private $totalSpent = 0;
    private $applyTo = '';
    private $limit = 1;
    private $give = 0;
    private $discount = 0;
    private $reduction = 0;
    private $totalItems = 0;
    private $minimum = 0;
    
    // value change logger
    protected $logger = array();

    // can take 2 DiscountBuilders in case i'd like to merge them, bu nothing is really using this yet.
    protected function __construct(DiscountBuilder $externalBuilder = null, DiscountBuilder $externalBuilder2 = null) {
        if ($externalBuilder != null) {
            $this->insert($externalBuilder);
            if ($externalBuilder2 != null) $this->insert($externalBuilder2);
            return $this;
        }
    }

    // a couple magic methods to handle build() having the same name statically and OO
    public function __call($prop, $args) {
        if ($prop === 'build') {
            $builder = (isset($args[0])) ? $args[0] : null;
            call_user_func(array($this, 'buildObject'), $builder);
        }
        return $this;
    }

    public static function __callStatic($prop, $args) {
        if ($prop === 'build') {
            $builder = (isset($args[0])) ? $args[0] : null;
            return call_user_func(array('self', 'buildStatic'), $builder);
        }
    }



    // static instantiation
    public static function buildStatic(DiscountBuilder $externalBuilder = null) {
        if ($externalBuilder != null) {
            return new self($externalBuilder);
        }
        return new self();
    }

    // object builder
    private function buildObject(DiscountBuilder $externalBuilder = null) {
        if ($externalBuilder != null) {
            $this->insert($externalBuilder);
        }
    }

    // one sided merge
    protected function insert(DiscountBuilder $externalBuilder) {

        // TODO recursive function here to cycle down arrays.
        // not required yet as nothing past the 1st dimension needs to be set independently of it's siblings
        foreach ($externalBuilder->logger as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->logger['$key']['$k'] = $this->{$key}[$k] = $v;
                }
            }
            else $this->logger[$key] = $this->{$key} = $value;
        }

        return $this;

    }


    // getters and setters


    public function getGroup() { return $this->group; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getDisabled() { return $this->disabled; }
    public function getTotalSpent() { return $this->totalSpent; }
    public function getApplyTo() { return $this->applyTo; }
    public function getLimit() { return $this->limit; }
    public function getGive() { return $this->give; }
    public function getDiscount() { return $this->discount; }
    public function getReduction() { return $this->reduction; }
    public function getTotalItems() { return $this->totalItems; }
    public function getMinimum() { return $this->minimum; }

    
    //  check internals of this one
    public function getfilterBy($filter) {
        return (isset($this->filterBy[$filter])) ? $this->filterBy[$filter] : null;
    }


    public function group(Int $group) {
        $this->logger['group'] = $this->group = $group;
        return $this;
    }


    public function name(String $name) {
        $this->logger['name'] = $this->name = $name;
        return $this;
    }


    public function description(String $description) {
        $this->logger['description'] = $this->description = $description;
        return $this;
    }


    public function filterBy(String $prop, String $value) {
        $this->logger['filterBy'][$prop] = $this->filterBy[$prop] = $value;
        return $this;
    }


    public function disabled(String $disabled) {
        $this->logger['disabled'] = $this->disabled = $disabled;
        return $this;
    }


    public function totalSpent(String $totalSpent) {
        $this->logger['totalSpent'] = $this->totalSpent = $totalSpent;
        return $this;
    }


    public function applyTo(String $applyTo) {
        $this->logger['applyTo'] = $this->applyTo = $applyTo;
        return $this;
    }


    public function limit(String $limit) {
        $this->logger['limit'] = $this->limit = $limit;
        return $this;
    }


    public function give(String $give) {
        $this->logger['give'] = $this->give = $give;
        return $this;
    }


    public function discount(String $discount) {
        $this->logger['discount'] = $this->discount = $discount;
        return $this;
    }


    public function reduction(String $reduction) {
        $this->logger['reduction'] = $this->reduction = $reduction;
        return $this;
    }


    public function totalItems(String $totalItems) {
        $this->logger['totalItems'] = $this->totalItems = $totalItems;
        return $this;
    }


    public function minimum(String $minimum) {
        $this->logger['minimum'] = $this->minimum = $minimum;
        return $this;
    }


}