<?php

namespace App\Containers;

use App\Contracts\DiscountServiceContainerContract;
use App\Contracts\Repositories\CustomerRepositoryContract;
use App\Contracts\Repositories\ProductRepositoryContract;
use App\Services\DiscountService;
use App\DataModels\Order;
use App\HookLoaders\DiscountHookLoader;
use Countable;

// return an instance of DiscountService
class DiscountServiceContainer implements DiscountServiceContainerContract, Countable {

    protected $service;

    // saved resources
    protected $customerRepository;
    protected $productRepository;
    protected $discounts = array();
    protected $orders = array();

    public function __construct(CustomerRepositoryContract $customer, ProductRepositoryContract $product) {
        $this->customerRepository = $customer;
        $this->productRepository = $product;
    }

    public function count() {
        return count($this->orders);
    }


    // the discount source
    // TODO make compatable with array, single discount with or without hookloader
    public function setSource($source) {
        $this->discounts = (new DiscountHookLoader())->load();
    }

    public function addOrder(Order $order) {

        // single customer
        $order->customer = $this->customerRepository->findById($order);

        // multiple products
        $products = array();
        foreach ($order->items as $item) {
            $products[] = $this->productRepository->findById($item);
        }
        $order->products = $products;

        // add the order
        $this->orders[] = $order;
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

    // make a new discount container
    public function make() {
        // create the service
        $this->service = new DiscountService($this->orders, $this->discounts);
        $this->service->applyDiscounts();
        return $this->service->getFinishedOrders();
    }

}