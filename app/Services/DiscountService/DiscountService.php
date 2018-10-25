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

    private function applyDiscount(Discount $discountObject) {

        // first let's see which orders meet the filter criteria for a discount, then worry about the rewards :E
        if ($filter = $discountObject->getFilterBy()) {
            $filterOrders = array_filter($this->orders, function($order) use ($discountObject) {

                $this->discountFilterService->clear();
                $this->discountFilterService->addItemData($order);

                // cycle through filters
                if (is_array($discountObject->getFilterBy())):

                    $this->fail['productIds'] = array();
                    $this->fail['categoryIds'] = array();
                    $this->fail['productEquality'] = array();
                    $this->fail['categoryEquality'] = array();

                    foreach ($discountObject->getFilterBy() as $filterKey => $filterType):
                        // if you specify without any filters
                        if (!count($filterType)) return false;
                        if (method_exists($this->discountFilterService, $filterKey))
                            $this->discountFilterService->{$filterKey}($filterType);
                        else
                            return false;
                    endforeach;


                    //item filtering after knmowing what fails.
                    $validProducts = $this->discountFilterService->filterValidProducts();


                    // fail checks
                    // validate the product sum, now that we have a list of valid products
                    if (isset($discountObject->getFilterBy()['product']['productSum'])) {
                        if (($productSum = $discountObject->getFilterBy()['product']['productSum']) !== null) {
                            if (!$this->discountFilterService->validProductSum($productSum, count($validProducts))) {
                                return false;
                            }
                        }
                    }

                    //compare with count of orders->items
                    if (!count($validProducts)) {
                        if ($this->discountFilterService->noValidOptions()) return false;
                    }

                    // invalidate last of questionable items
                    if (!$this->discountFilterService->orderStatus()) return false;
                    if (!$this->discountFilterService->lifetimeSpendStatus()) return false;


                    // the discount is good for this order, apply the reward
                    if ($rewardType = $discountObject->getRewardType()) {
                        if (method_exists($this->discountRewardService, $rewardType)) {
                            $this->discountRewardService->{$rewardType}($order, $validProducts);
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