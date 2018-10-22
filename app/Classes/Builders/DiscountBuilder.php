<?php

namespace App\Builders;

// now derived from generic builder class
class DiscountBuilder extends Builder {

    // add stuff on instantiation if you need
    protected function __construct(Builder $externalBuilder = null) {
        parent::__construct($externalBuilder);
    }

    // an overview because its needed ;x
    // these are all based on AND, not OR

    // im using this mostly to make validation easier as to whether or not something can be set
    // its only needed if you use functions addIfSet() and addWithItemIfSet()

    // field template
    protected $template = array(
        'group' => 1,
        'description' => 1,
        'name' => 1,
        'rewardType' => 1,
        'rewardNumber' => 1,
        'applyRewardTo' => 1,
        'priority' => 1,
        'limit' => 1,            
        'each' => array(
            'totalSpent' => 1,
            'totalItems' => 1,
            'applyTo' => 1,
        ),
        'filterBy' => array(
            'product' => array(
                'id' => 1,
                'price' => array(
                    'moreThan' => 1,
                    'lessThan' => 1,
                    'moreThanEquals' => 1,
                    'lessThanEquals' => 1,
                    'equals' => 1,
                ),
                'itemSum' => array(
                    'moreThan' => 1,
                    'lessThan' => 1,
                    'moreThanEquals' => 1,
                    'lessThanEquals' => 1,
                    'equals' => 1,
                ),
            ),
            'category' => array(
                'id' => 1,
                'price' => array(
                    'moreThan' => 1,
                    'lessThan' => 1,
                    'moreThanEquals' => 1,
                    'lessThanEquals' => 1,
                    'equals' => 1,
                ),
                'itemSum' => array(
                    'moreThan' => 1,
                    'lessThan' => 1,
                    'moreThanEquals' => 1,
                    'lessThanEquals' => 1,
                    'equals' => 1,
                ),
            ),
            'order' => array(
                'price' => array(
                    'moreThan' => 1,
                    'lessThan' => 1,
                    'moreThanEquals' => 1,
                    'lessThanEquals' => 1,
                    'equals' => 1,
                ),
                'itemSum' => array(
                    'moreThan' => 1,
                    'lessThan' => 1,
                    'moreThanEquals' => 1,
                    'lessThanEquals' => 1,
                    'equals' => 1,
                ),
            ),
            'lifetimeSpend' => array(
                'moreThan' => 1,
                'lessThan' => 1,
                'moreThanEquals' => 1,
                'lessThanEquals' => 1,
                'equals' => 1, 
            ),
        ),
        'enabled' => 1
    );

    // properties
    protected $group = 0;
    protected $description = '';
    protected $name = '';
    protected $rewardType = null;
    protected $rewardNumber = null;
    protected $applyRewardTo = null;
    protected $priority = 'cheapest';
    protected $limit = 1;
    protected $each = array();
    protected $filterBy = array();
    protected $enabled = 'true';

    
    // setters
    // logging changes to merge objects correctly
    public function group(Int $group) {
        $this->addIfSet(__FUNCTION__, $group);
        return $this;
    }


    public function name(String $name) {
        $this->addIfSet(__FUNCTION__, $name);
        return $this;
    }


    public function description(String $description) {
        $this->addIfSet(__FUNCTION__, $description);
        return $this;
    }


    public function rewardType(String $rewardType) {
        $this->addIfSet(__FUNCTION__, $rewardType);
        return $this;
    }


    public function rewardNumber(String $rewardNumber) {
        $this->addIfSet(__FUNCTION__, $rewardNumber);
        return $this;
    }


    public function applyRewardTo(String $applyRewardTo) {
        $this->addIfSet(__FUNCTION__, $applyRewardTo);
        return $this;
    }


    public function priority(String $priority) {
        $this->addIfSet(__FUNCTION__, $priority);
        return $this;
    }


    public function limit(String $limit) {
        $this->addIfSet(__FUNCTION__, $limit);
        return $this;
    }


    public function each(String $each, $value = null) {
        $this->addWithItemIfSet(__FUNCTION__, $each, $value);
        return $this;
    }


    public function filterBy(String $filterBy, $value = null) {
        $this->addWithItemIfSet(__FUNCTION__, $filterBy, $value);
        return $this;
    }


    public function enabled(String $enabled) {
        $this->addIfSet(__FUNCTION__, $enabled);
        return $this;
    }

}