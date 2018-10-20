<?php

namespace App\Services;

use App\Datamodels\CustomerDataModel;
use App\Datamodels\ProductDataModel;
use App\Datamodels\OrderDataModel;
use App\Builders\Discount;
use Countable;

class DiscountService implements Countable {


    // dependencies
    private $discounts = array();
    private $processedDiscounts = array();
    private $customerdata = array();
    private $productdata = array();


    // orders
    private $orders = array();
    private $finishedOrders = array();


    /*
        constructor takes an associative array
        properties are: 'discounts', 'customerdata', 'productdata'
        proprties can be a single object, or an array of objects
    */
    public function __construct(Array $args) {
        $discounts = $args['discounts'];
        $customerdata = $args['customerdata'];
        $productdata = $args['productdata'];

        $this->discounts = $discounts;
        $this->insert('customerdata', CustomerDataModel::class, $customerdata);
        $this->insert('productdata', ProductDataModel::class, $productdata);
    }


    public function count() {
        return count($this->orders);
    }


    // help tidy things up in the constructor
    private function insert(String $property, $model, $items) {
        $prop = $this->{$property};

        if ($items instanceof $model) {
            $this->{$property}[] = $items;
        }

        elseif (is_array($items) && count($items) > 0) {
            foreach ($items as $item) {
                if ($item instanceof $model) {
                    $this->{$property}[] = ($item);
                }
            }
        }
    }


    // add order to the batch
    public function add(OrderDataModel $order) {
        // get user model
        $customerArray = (Array) $this->customerdata;

        // single customer
        $customer = array_filter($this->customerdata, function($data) use ($order) {
            return $data->id == $order->customerid;
        });

        // multiple products
        $products = array();
        foreach ($order->items as $item) {
            $products[] = array_filter($this->productdata, function($product) use ($item) {
                return $product->id == $item['product-id'];
            });
        }

        // create properties on the order object
        $order->customerModel = $customer;
        $order->productModels = $products;

        $this->orders[] = $order;
        return $this;
    }

    // apply discounts to all $orders
    public function applyDiscounts() {
        $orders = $this->orders;
        $newDiscounts = array();

        foreach ($this->discounts as $discount) {
            foreach ($orders as $order) {
                $discount->generate($order);
                $newDiscounts[] = $discount;
            }
        }

        foreach ($orders as $order) {
            $this->finishedOrders[] = $order;
        }

        $this->processedDiscounts = $newDiscounts;
        $this->discounts = array();
        $this->orders = array();
        return $this;
    }

    // get array of orders with discount applied
    public function get() {
        $return = $this->finishedOrders;
        $this->finishedOrders = array();
        return $return;
    }

}