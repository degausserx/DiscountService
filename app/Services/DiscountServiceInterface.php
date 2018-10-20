<?php

namespace App\Services;

use App\Datamodels\OrderDataModel;

interface DiscountServiceInterface {

    // count items (orders) in service
    public function count();

    // add order to the batch - maybe move most of logic to some sort of factory creation tool
    public function add(OrderDataModel $order);

    // apply discounts to all $orders
    public function applyDiscounts();

    // get array of orders with discount applied
    public function get();
}