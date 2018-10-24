<?php

namespace App\Builders;

// now derived from generic builder class
class DiscountBuilder extends Builder {

    // add stuff on instantiation if you need
    protected function __construct(Builder $externalBuilder = null) {
        parent::__construct($externalBuilder);
    }

    // im using this mostly to make validation easier as to whether or not something can be set
    // its only needed if you use functions addIfSet() and addWithItemIfSet()

    // field template
    protected $template = array(
        'description' => '',
        'name' => '',
        'rewardType' => '',
        'rewardNumber' => '',
        'applyRewardTo' => '',
        'priority' => '',
        'limit' => '',            
        'each' => array(
            'totalSpent' => '',
            'totalItems' => '',
            'applyTo' => '',
        ),
        'filterBy' => array(
            'product' => array(
                'id' => '',
                'price' => array(
                    'moreThan' => '',
                    'lessThan' => '',
                    'moreThanEquals' => '',
                    'lessThanEquals' => '',
                    'equals' => '',
                ),
                'itemSum' => array(
                    'moreThan' => '',
                    'lessThan' => '',
                    'moreThanEquals' => '',
                    'lessThanEquals' => '',
                    'equals' => '',
                ),
                'productSum' => array(
                    'moreThan' => '',
                    'lessThan' => '',
                    'moreThanEquals' => '',
                    'lessThanEquals' => '',
                    'equals' => '',
                ),
            ),
            'category' => array(
                'id' => '',
            ),
            'order' => array(
                'price' => array(
                    'moreThan' => '',
                    'lessThan' => '',
                    'moreThanEquals' => '',
                    'lessThanEquals' => '',
                    'equals' => '',
                ),
                'itemSum' => array(
                    'moreThan' => '',
                    'lessThan' => '',
                    'moreThanEquals' => '',
                    'lessThanEquals' => '',
                    'equals' => '',
                ),
            ),
            'lifetimeSpend' => array(
                'moreThan' => '',
                'lessThan' => '',
                'moreThanEquals' => '',
                'lessThanEquals' => '',
                'equals' => '', 
            ),
        ),
    );
    

    public function name($name) {
        $this->addIfSet(__FUNCTION__, $name);
        return $this;
    }


    public function description($description) {
        $this->addIfSet(__FUNCTION__, $description);
        return $this;
    }


    public function rewardType($rewardType) {
        $this->addIfSet(__FUNCTION__, $rewardType);
        return $this;
    }


    public function rewardNumber($rewardNumber) {
        $this->addIfSet(__FUNCTION__, $rewardNumber);
        return $this;
    }


    public function applyRewardTo($applyRewardTo) {
        $this->addIfSet(__FUNCTION__, $applyRewardTo);
        return $this;
    }


    public function priority($priority) {
        $this->addIfSet(__FUNCTION__, $priority);
        return $this;
    }


    public function limit($limit) {
        $this->addIfSet(__FUNCTION__, $limit);
        return $this;
    }


    public function each($each, $value = null) {
        $this->addWithItemIfSet(__FUNCTION__, $each, $value);
        return $this;
    }


    public function filterBy($filterBy, $value = null) {
        $this->addWithItemIfSet(__FUNCTION__, $filterBy, $value);
        return $this;
    }


}