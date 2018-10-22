<?php

namespace App\Contracts;

use App\Datamodels\Order;
use App\Objects\Discounts\Discount;

interface DiscountServiceContract {

    // count items (orders) in service
    public function count();

    // add order to the batch - maybe move most of logic to some sort of factory creation tool
    public function addOrder(Order $order);

    // add array of orders to the batch
    public function addOrders(Array $orders);

    // add discount to the batch
    public function setDiscount(Discount $discount);

    // add array of discounts to the batch
    public function setDiscounts(Array $discount);

    // get an array of orders back
    public function getOrders();

    // clear discounts table
    public function clearDiscounts();

    // clear order table. 
    public function clearOrders();

    // apply discounts to all $orders, clears orders table
    public function applyDiscounts();
    
}
