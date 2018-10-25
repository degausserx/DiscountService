<?php

namespace App\Services\DiscountService;

use App\Contracts\DiscountServiceContract;
use App\Services\DiscountService\Rewards;
use App\Services\DiscountService\Filter;
use App\HookLoaders\DiscountHookLoader;
use App\Datamodels\Order;
use App\Objects\Discounts\Discount;
use Countable;

// just using 1 file to quicken things up. Could be a good idea to make a DiscountService folder
// so that you can split the logic of filtering orders / products, working out the reward, apply it, etc
class DiscountService implements DiscountServiceContract, Countable {

    // dependencies
    private $discountRewardService;
    private $discountFilterService;

    private $discounts = array();
    private $orders = array();

    private $success = array();
    private $fail = array();

    public function __constuct() {
        $this->discountRewardService = new Rewards();
        $this->discountFilterService = new Filter();
    }

    public function count() {
        return count($this->orders);
    }

    // add order to the batch
    public function addOrder(Order $order) {
        $this->orders[] = $order;
    }

    // add array of orders to the batch
    public function addOrders(Array $orders) {
        foreach ($orders as $order) $this->addOrder($order);
    }

    // get an array of orders
    public function getOrders() {
        return $this->orders;
    }

    // add discount to the batch
    public function setDiscount(Discount $discount) {
        $this->discounts = array($discount);
    }

    // add array of discounts to the batch
    public function setDiscounts(Array $discounts) {
        $this->discounts = $discounts;
    }

    public function clearDiscounts() {
        $this->discounts = array();
    }

    public function clearOrders() {
        $this->orders = array();
    }

    // apply discounts to all $orders
    public function applyDiscounts() {

        // send individual discount to next function
        foreach ($this->discounts as $discount) {
            $this->discountRewardService = new Reward($discount);
            $this->discountFilterService = new Filter($discount);
            $this->applyDiscount($discount);
        }

        return $this;
    }

    // help methods for the filtering to come >>
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

    private function applyDiscount(Discount $discountObject) {

        // first let's see which orders meet the filter criteria for a discount, then worry about the rewards :E
        if ($filter = $discountObject->getFilterBy()) {
            $filterOrders = array_filter($this->orders, function($order) use ($discountObject) {

                // get total items / ids
                $mainCategories = array();
                $mainProducts = array();
                $mainCategoryIds = array();
                $mainProductIds = array();

                $itemCount = 0;
                $customer = $order['customer'];

                foreach ($order['products'] as $subitem) {
                    foreach ($subitem as $item) {
                        $mainProductIds[] = $id = $item->id;
                        $mainProducts[$id] = array();
                        $mainProducts[$id]['category'] = $item->category;
                        $mainCategoryIds[] = $item->category;
                    }
                }

                foreach ($order->items as $item) {
                    $cat = $item['product-id'];
                    if (!isset($mainProducts[$cat])) $mainProducts[$cat] = array();
                    $mainProducts[$cat]['quantity'] = $item['quantity'];
                    $mainProducts[$cat]['unit-price'] = $item['unit-price'];
                    $mainProducts[$cat]['total-price'] = $item['total'];
                }

                $this->success = array();
                $this->fail = array();

                // cycle through filters
                if (is_array($discountObject->getFilterBy())):

                    $this->fail['productIds'] = array();
                    $this->fail['categoryIds'] = array();
                    $this->fail['productEquality'] = array();
                    $this->fail['categoryEquality'] = array();

                    foreach ($discountObject->getFilterBy() as $filterKey => $filterType):
                        // if you specify without any filters
                        if (!count($filterType)) return false;


                        // lifetime spend
                        if ($filterKey == 'lifetimeSpend' && is_array($filterType)):
                            //$discountFilterService->lifetimeSpend($filterType);
                            $lifetimeSpend = $customer->revenue;
                            foreach ($filterType as $key => $value):
                                if (!$this->{$key}($value, $lifetimeSpend)):
                                    $this->fail[$filterKey] = true;
                                    // lifetime spend requirement failed
                                else:
                                    $this->success[$filterKey] = true;
                                endif;
                            endforeach;



                        // order
                        elseif ($filterKey == 'order' && is_array($filterType)):
                            foreach ($filterType as $key => $value):
                                if (is_array($value)):
                                    foreach ($value as $propery => $item):
                                        if ($key == 'price') $compare = $order->total;
                                        elseif ($key == 'itemSum') $compare = $itemCount;
                                        else break;
                                        if (!$this->{$propery}($item, $compare)):
                                            $this->fail[$filterKey] = true;
                                            // order has failed
                                        else:
                                            $this->success[$filterKey] = true;
                                        endif;
                                    endforeach;
                                endif;
                            endforeach;;



                        // product
                        elseif ($filterKey == 'product' && is_array($filterType)):
                            foreach ($filterType as $key => $value):

                                // the ID part
                                if ($key == 'id' && $value):
                                    $itemId = explode('|', $value);
                                    foreach ($itemId as $valueId):
                                        if (!in_array($valueId, $mainProductIds)):
                                            $this->fail['productIds'][] = $valueId;
                                            // fail
                                        else:
                                            $this->success['productIds'][] = $valueId;
                                        endif;
                                    endforeach;
                                    if (count($itemId) <= count($this->fail['productIds'])):
                                        return false;
                                    endif;



                                // the Equality part
                                elseif (in_array($key, array('price', 'itemSum')) && $value):
                                    $arrayItem = ($key == 'price') ? 'unit-price' : 'quantity';
                                    foreach ($value as $property => $piece):
                                        $mItem = null;
                                        foreach ($order->items as $item):
                                            $mItem = $item['product-id'];
                                            if (!$this->{$property}($piece, $mainProducts[$mItem][$arrayItem])):
                                                $this->fail['productEquality'][$mItem][] = $key;
                                            else:
                                                $this->success['productEquality'][$mItem][] = $key;
                                            endif;
                                        endforeach;
                                        if (isset($this->fail['productEquality'][$mItem]) && count($order->items) <= count($this->fail['productEquality'])):
                                            $this->fail['productIds'][] = $mItem;
                                        endif;
                                    endforeach;



                                endif;
                            endforeach;
                        // category
                        elseif ($filterKey == 'category' && is_array($filterType)):
                            foreach ($filterType as $key => $value):



                                // the ID part
                                if ($key == 'id' && $value):
                                    $itemId = explode('|', $value);
                                    foreach ($itemId as $valueId):
                                        if (!in_array($valueId, $mainCategoryIds)):
                                            $this->fail['categoryIds'][] = $valueId;
                                            // fail
                                        else:
                                            $this->success['categoryIds'][] = $valueId;
                                        endif;
                                    endforeach;
                                    if (count($itemId) <= count($this->fail['categoryIds'])):
                                        return false;
                                    endif;
                                endif;



                            endforeach;
                        else:
                            // no valid filter type
                            return false;
                        endif;
                    endforeach;


                    // final filtering of valid products
                    $goodItems = array();

                    //item filtering after knmowing what fails.
                    foreach ($order->products as $productObject) {
                        foreach ($productObject as $product) {
                            $id = $product->id;
                            //var_dump($this->fail['categoryIds']);
                            if (!in_array($id, $this->fail['productIds']) &&
                                !in_array($product->category, $this->fail['categoryIds']) &&
                                !isset($this->fail['productEquality'][$id]) &&
                                !isset($this->fail['categoryEquality'][$id])) {
                                
                                // product passed all tests
                                $goodItems[] = $id;
                            }

                        }
                    }

                    // validate the product sum, now that we have a list of valid products
                    if (isset($discountObject->getFilterBy()['product']['productSum'])) {
                        if (($productSum = $discountObject->getFilterBy()['product']['productSum']) !== null) {
                            foreach ($productSum as $property => $piece) {
                                //print("$property $piece" . count($goodItems));
                                if (!$this->{$property}($piece, count($goodItems))) {
                                    return false;
                                    // fail
                                }
                            }
                        }
                    }

                    //compare with count of orders->items
                    if (!count($goodItems)) {
                        if (count($this->fail['productEquality']) ||
                            count($this->fail['productIds']) ||
                            count($this->fail['categoryEquality']) ||
                            count($this->fail['categoryIds'])) {
                            return false;
                        }
                    }

                    // invalidate last of questionable items
                    if (isset($this->fail['order'])) return false;
                    if (isset($this->fail['lifetimeSpend'])) return false;


                    // the discount is good for this order, apply the reward
                    if ($rewardType = $discountObject->getRewardType()) {
                        if (method_exists($this->discountRewardService, $rewardType)) {
                            $this->discountRewardService->{$rewardType}($order, $goodItems);
                        }
                    }

                    return true;

                else:

                    return false;

                endif;
            });

        }

        else {
          
            // no filters. the minimum you can do is specify order.itemSum > 0, or something

        }

    }


}