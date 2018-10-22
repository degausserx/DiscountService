<?php

namespace App\Contracts;

use App\DataModels\Order;

interface DiscountServiceContainerContract {

    public function count();

    public function setDiscounts($source);

    public function clearDiscounts();

    public function clearOrders();

    public function addOrder(Order $order);

    public function addOrders(Array $orders);

    public function getOrder(Int $index);

    public function getOrders();

    public function generate();

}