<?php

namespace App\Contracts;

use App\DataModels\Order;

interface DiscountServiceContainerContract {

    public function count();

    public function setSource($source);

    public function addOrder(Order $order);

    public function addOrders(Array $orders);

    public function getOrder(Int $index);

    public function getOrders();

    public function make();

}