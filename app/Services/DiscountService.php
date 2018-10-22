<?php

namespace App\Services;

use App\Contracts\DiscountServiceContract;
use App\HookLoaders\DiscountHookLoader;
use App\Datamodels\Order;
use App\Objects\Discounts\Discount;
use Countable;

class DiscountService implements DiscountServiceContract, Countable {

    // dependencies
    private $discounts = array();
    private $orders = array();
    private $finishedOrders = array();

    public function count() {
        return count($this->finishedOrders);
    }

    // add order to the batch
    public function addOrder(Order $order) {
        $this->orders[] = $order;
    }

    // add array of orders to the batch
    public function addOrders(Array $orders) {
        foreach ($orders as $order) $this->addOrder($order);
    }

    // get an array of orders
    public function getOrders() {
        return $this->orders;
    }

    // add discount to the batch
    public function setDiscount(Discount $discount) {
        $this->discounts = array($discount);
    }

    // add array of discounts to the batch
    public function setDiscounts(Array $discounts) {
        $this->discounts = $discounts;
    }

    public function clearDiscounts() {
        $this->discounts = array();
    }

    public function clearOrders() {
        $this->orders = array();
    }

    // apply discounts to all $orders
    public function applyDiscounts() {
        $orders = $this->orders;

        foreach ($this->discounts as $discount) {
            foreach ($this->orders as $order) {
                $this->applyDiscount($order, $discount);
            }
        }

        return $this;
    }

    // okay the work starts here
    private function applyDiscount(Order $order, Discount $discountObject) {
        $discount = $discountObject->getData();

        // var_dump($discount);
        //
    }

}