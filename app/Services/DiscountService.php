<?php

namespace App\Services;

use App\Contracts\DiscountServiceContract;
use App\Datamodels\Order;
use App\Builders\Discount;
use Countable;

class DiscountService implements DiscountServiceContract, Countable {


    // dependencies
    private $discounts = array();
    private $orders = array();
    private $finishedOrders = array();


    public function __construct(Array $orders, Array $discounts) {
        $this->orders = $orders;
        $this->discounts = $discounts;
    }


    public function count() {
        return count($this->finishedOrders);
    }

    // add order to the batch
    public function add(Order $order) {
        $this->orders[] = $order;
        return $this;
    }

    // apply discounts to all $orders
    public function applyDiscounts() {
        $orders = $this->orders;

        foreach ($this->discounts as $discount) {
            foreach ($this->orders as $order) {
                $discount->generate($order);
            }
        }

        foreach ($orders as $order) {
            $this->finishedOrders[] = $order;
        }

        $this->orders = array();
        return $this;
    }

    // get array of orders with discount applied
    public function getFinishedOrders() {
        $return = $this->finishedOrders;
        $this->finishedOrders = array();
        return $return;
    }

}