<?php

namespace App\Services\DiscountService;

use App\Repositories\CustomerRepository;
use App\Repositories\ProductRepository;
use App\Objects\Discounts\Discount;
use App\Datamodels\Order;

class Filter {

    private $customerRepository;
    private $productRepository;

    private $fail;
    private $order;

    private $mainProducts;
    private $mainCategoryIds;
    private $mainProductIds;
    private $itemCount;
    private $customer;
    private $products;

    public function __construct(CustomerRepository $customer, ProductRepository $product) {
        $this->customerRepository = $customer;
        $this->productRepository = $product;
    }

    // setup some data to use with filtering
    public function addItemData(Order $order) {
        
        // declarations
        $this->order = $order;
        $this->products = array();
        $this->customer = null;
        $this->fail = array();
        $this->itemCount = 0;

        $this->mainProducts = array();
        $this->mainCategoryIds = array();
        $this->mainProductIds = array();

        $this->fail['productIds'] = array();
        $this->fail['categoryIds'] = array();
        $this->fail['productEquality'] = array();

        try {
            // get customer information from repo
            $this->customer = $this->customerRepository->findById($order->id);
            
            // get product information from repo
            $this->products = array();
            foreach ($order->items as $item) {
                $this->products[] = $this->productRepository->findById($item['product-id']);
            }
        }
        
        catch(Exception $e) {
            return false;
        }


        foreach ($this->products as $object) {
            foreach ($object as $item) {
                $this->mainProductIds[] = $id = $item->id;
                $this->mainProducts[$id] = array();
                $this->mainProducts[$id]['category'] = $item->category;
                $this->mainCategoryIds[] = $item->category;
            }
        }


        foreach ($order->items as $item) {
            $id = $item['product-id'];
            if (!isset($this->mainProducts[$id])) $this->mainProducts[$id] = array();
            $this->mainProducts[$id]['quantity'] = $item['quantity'];
            $this->mainProducts[$id]['unit-price'] = $item['unit-price'];
            $this->mainProducts[$id]['total-price'] = $item['total'];
            $this->itemCount += $item['quantity'];
        }

        return true;

    }

    // returns true if order hasn't failed
    public function orderStatus() {
        return !isset($this->fail['order']);
    }

    // returns true if revenue flters haven't failed
    public function lifetimeSpendStatus() {
        return !isset($this->fail['lifetimeSpend']);
    }

    // returns true if any of the product filters failed
    public function validOptions() {
        if (count($this->fail['productEquality']) ||
            count($this->fail['productIds']) ||
            count($this->fail['categoryIds'])) {
            return false;
        }
        return true;
    }


    // functions to compare values
    protected function moreThan($discountValue, $orderValue) {
        return $orderValue > $discountValue;
    }

    protected function lessThan($discountValue, $orderValue) {
        return $orderValue < $discountValue;
    }

    protected function moreThanEqual($discountValue, $orderValue) {
        return $orderValue >= $discountValue;
    }

    protected function lessThanEqual($discountValue, $orderValue) {
        return $orderValue <= $discountValue;
    }

    protected function equals($discountValue, $orderValue) {
        return $orderValue == $discountValue;
    }

    // main filters
    // add the ids of all products which fail a filter to $this->fail['productIds']

    // lifetime revenue
    public function lifetimeSpend(Array $filterType) {
        foreach ($filterType as $key => $value):
            if (!$this->{$key}($value, $this->customer->revenue)):
                return false;
            endif;
        endforeach;

        return true;
    }

    // order price / item sum
    public function order(Array $filterType) {
        foreach ($filterType as $key => $value):
            if (is_array($value)):
                foreach ($value as $propery => $item):
                    if ($key == 'price') $compare = $this->order->total;
                    elseif ($key == 'itemSum') $compare = $this->itemCount;
                    else break;
                    if (!$this->{$propery}($item, $compare)):
                        return false;
                    endif;
                endforeach;
            endif;
        endforeach;

        return true;
    }

    // category
    public function category(Array $filterType) {
        foreach ($filterType as $key => $value)
            // the ID part
            if ($key == 'id' && strlen($value))
                $this->processId('categoryIds', $this->mainCategoryIds, $value);
    }

    // product
    public function product(Array $filterType) {
        foreach ($filterType as $key => $value):
            // the ID part
            if ($key == 'id' && strlen($value)):
                $this->processId('productIds', $this->mainProductIds, $value);

            // the Equality part
            elseif (in_array($key, array('price', 'itemSum')) && is_array($value)):
                $arrayItem = ($key == 'price') ? 'unit-price' : 'quantity';
                foreach ($value as $property => $piece):
                    $mItem = null;
                    foreach ($this->order->items as $item):
                        $mItem = $item['product-id'];
                        if (!$this->{$property}($piece, $this->mainProducts[$mItem][$arrayItem])):
                            $this->fail['productEquality'][$mItem][] = $key;
                        endif;
                    endforeach;
                    if (isset($this->fail['productEquality'][$mItem]) && count($this->order->items) <= count($this->fail['productEquality'][$mItem])):
                        $this->fail['productIds'][] = $mItem;
                    endif;
                endforeach;
            endif;
        endforeach;
    }

    // sum of unique products
    public function validProductSum(Array $productSum, Int $count) {
        foreach ($productSum as $property => $piece) {
            if (!$this->{$property}($piece, $count)) {
                return false;
            }
        }
        return true;
    }

    // ids
    public function processId(String $key, Array $ids, $value) {
        $itemId = explode('|', $value);
        foreach ($ids as $productId):
            if (!in_array($productId, $itemId)):
                $this->fail[$key][] = $productId;
            endif;
        endforeach;
    }

    // final valid product list
    public function filterValidProducts() {
        $validItems = array();
        foreach ($this->products as $productObject) {
            foreach ($productObject as $product) {
                $id = $product->id;
                if (!in_array($id, $this->fail['productIds']) &&
                    !in_array($product->category, $this->fail['categoryIds']) &&
                    !isset($this->fail['productEquality'][$id])) {
                    
                    // product passed all tests
                    $validItems[] = $id;
                }
            }
        }

        return $validItems;
    }

}