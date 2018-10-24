<?php

namespace App\Repositories;

use App\Contracts\Repositories\OrderRepositoryContract;
use App\Contracts\Repositories\CustomerRepositoryContract;
use App\Contracts\Repositories\ProductRepositoryContract;
use App\DataModels\Order;
use Countable;

class OrderRepository implements OrderRepositoryContract, Countable {

    private $orders;
    protected $customerRepository;
    protected $productRepository;

    public function __construct(CustomerRepositoryContract $customer, ProductRepositoryContract $product) {
        $this->customerRepository = $customer;
        $this->productRepository = $product;
    }

    private function addOrderDependencies(Order $order) {
        // single customer
        $order->customer = $this->customerRepository->findById($order->id);

        // multiple products
        $products = array();
        foreach ($order->items as $item) {
            $products[] = $this->productRepository->findById($item['product-id']);
        }

        $order->products = $products;

        return $order;
    }

    public function count() {
        return count($this->orders);
    }

    public function add(Order $order) {
        $order = $this->addOrderDependencies($order);
        $this->orders[] = $order;
        return $order;
    }
    
    public function make(Array $order) {
        return $this->addOrderDependencies(Order::make($order));
    }

    public function remake(Order $order) {
        return $this->addOrderDependencies($order);
    }

    public function get(Int $int) {
        if ($int < 1) return $this->getAll();
        return (isset($this->orders[$int])) ? $this->orders[$int] : null;
    }

    public function getAll() {
        return $this->orders;
    }

    public function findById($x) {
        return array_filter((Array) $this->orders, function($order) use ($x) {
            return $order['id'] === $x;
        });
    }

}