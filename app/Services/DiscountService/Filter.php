<?php

namespace App\Services\DiscountService;

use App\Objects\Discounts\Discount;
use App\Datamodels\Order;

class Filter {

    private $success = array();
    private $fail = array();
    private $order;

    private $mainCategories = array();
    private $mainProducts = array();
    private $mainCategoryIds = array();
    private $mainProductIds = array();
    private $itemCount;
    private $customer;

    public function __construct() {

    }

    public function clear() {
        $this->success = array();
        $this->fail = array();
        $this->order = null;
    }


    // setup some data to use with filtering
    public function addItemData(Order $order) {

        // set current order;
        $this->order = $order;

        $this->itemCount = 0;
        $this->customer = $order['customer'];

        $this->mainCategories = array();
        $this->mainProducts = array();
        $this->mainCategoryIds = array();
        $this->mainProductIds = array();

        foreach ($order['products'] as $subitem) {
            foreach ($subitem as $item) {
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
            if (!isset($this->mainCategories[$id])) $this->mainCategories[$id] = array();
            if (!isset($this->mainCategories[$id]['unit-price'])) $this->mainCategories[$id]['unit-price'] = 0;
            $this->mainCategories[$id]['unit-price'] += $item['total'];
            $this->itemCount += $item['quantity'];
        }

        $this->fail['productIds'] = array();
        $this->fail['categoryIds'] = array();
        $this->fail['productEquality'] = array();
        $this->fail['categoryEquality'] = array();

    }

    public function orderStatus() {
        return !isset($this->fail['order']);
    }

    public function lifetimeSpendStatus() {
        return !isset($this->fail['lifetimeSpend']);
    }

    public function noValidOptions() {
        return (!isset($this->success['lifetimeSpend']) && !isset($this->success['order']));
    }

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

    // lifetime revenue
    public function lifetimeSpend(Array $filterType) {
        foreach ($filterType as $key => $value):
            if (!$this->{$key}($value, $this->customer->revenue)):
                $this->fail[__FUNCTION__] = true;
            else:
                $this->success[__FUNCTION__] = true;
            endif;
        endforeach;
    }

    // order price / item sum
    public function order(Array $filterType) {
        foreach ($filterType as $key => $value):
            if (is_array($value)):
                foreach ($value as $propery => $item):
                    if ($key == 'price') $compare = $order->total;
                    elseif ($key == 'itemSum') $compare = $itemCount;
                    else break;
                    if (!$this->{$propery}($item, $compare)):
                        $this->fail[__FUNCTION__] = true;
                        // order has failed
                    else:
                        $this->success[__FUNCTION__] = true;
                    endif;
                endforeach;
            endif;
        endforeach;
    }

    // category
    public function category(Array $filterType) {
        foreach ($filterType as $key => $value):
            // the ID part
            if ($key == 'id' && strlen($value))
                $this->processId('categoryIds', $this->mainCategoryIds, $value);
        endforeach;
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
                            // fail
                        else:
                            $this->success['productEquality'][$mItem][] = $key;
                        endif;
                    endforeach;
                    if (isset($this->fail['productEquality'][$mItem]) && count($order->items) <= count($this->fail['productEquality'][$mItem])):
                        $this->fail['productIds'][] = $mItem;
                    endif;
                endforeach;
            endif;
        endforeach;
    }

    // ids
    private function processId(String $key, Array $Ids, $value) {
        $this->fail[$key] = array();
        $itemId = explode('|', $value);
        foreach ($itemId as $valueId):
            if (!in_array($valueId, $Ids)):
                $this->fail[$key][] = $valueId;
            else:
                $this->success[$key][] = $valueId;
            endif;
        endforeach;
    }

    // final valid product list
    public function filterValidProducts() {
        $validItems = array();
        foreach ($this->order->products as $productObject) {
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

    public function validProductSum(Array $productSum, Int $count) {
        foreach ($productSum as $property => $piece) {
            if (!$this->{$property}($piece, $count)) {
                return false;
            }
        }
    }

}