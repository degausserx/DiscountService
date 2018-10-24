<?php

namespace App\Containers;

use App\Contracts\DiscountServiceContainerContract;
use App\Contracts\Repositories\OrderRepositoryContract;
use App\Contracts\DiscountServiceContract;
use App\DataModels\Order;
use App\Objects\Discounts\Discount;
use Countable;

// return an instance of DiscountService
class DiscountServiceContainer implements DiscountServiceContainerContract, Countable {

    protected $discountService;

    // saved resources
    protected $orderRepository;
    protected $discounts = array();
    protected $orders = array();

    public function __construct(OrderRepositoryContract $orderRepository, DiscountServiceContract $discountService) {
        $this->orderRepository = $orderRepository;
        $this->discountService = $discountService;
    }

    public function count() {
        return count($this->orders);
    }


    // set the discounts. it accepts an array of discounts, or a single Discount object
    public function setDiscounts($source) {
        if (is_array($source)) $this->discounts = $source;
        else $this->discounts = array($source);

        foreach ($this->discounts as $discount) {
            if (!($discount instanceof Discount)) throw new Exception("Invalid arguement, expecting a callable, Discount or an array of either");
        }
    }

    public function clearDiscounts() {
        $this->discountService->clearDiscounts();
        $this->discounts = array();
    }

    public function clearOrders() {
        $this->discountService->clearOrders();
        $this->orders = array();
    }

    // manage the orders
    public function addOrder(Order $order) {
        $this->orders[] = $this->orderRepository->remake($order);
    }

    public function addOrders(Array $orders) {
        foreach ($orders as $order) {
            if ($order instanceof Order) {
                $this->addOrder($order);
            }
        }
    }

    public function getOrder(Int $index) {
        if ($index < 1) return $this->getOrders();
        return (isset($this->orders[$index])) ? $this->orders[$index] : null;
    }

    public function getOrders() {
        return $this->orders;
    }

    // generate
    public function generate() {
        $this->discountService->addOrders($this->orders);
        $this->discountService->setDiscounts($this->discounts);
        $this->discountService->applyDiscounts();
    }

}