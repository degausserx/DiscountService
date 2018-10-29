<?php

namespace App\Services\DiscountService;

use App\Repositories\CustomerRepository;
use App\Repositories\ProductRepository;
use App\Services\DiscountService\Rewards;
use App\Services\DiscountService\Filter;
use App\Datamodels\Order;
use App\Objects\Discounts\Discount;
use Countable;

// just using 1 file to quicken things up. Could be a good idea to make a DiscountService folder
// so that you can split the logic of filtering orders / products, working out the reward, apply it, etc
class DiscountService implements Countable {

    // dependencies
    private $discountRewardService;
    private $discountFilterService;

    private $discounts = array();
    private $orders = array();

    private $customerRepository;
    private $productRepository;

    public function setRepositories(CustomerRepository $customerRepository, ProductRepository $productRepository) {
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
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

        $this->discountFilterService = new Filter($this->customerRepository, $this->productRepository);

        // send individual discount to next function
        foreach ($this->discounts as $discount) {
            $this->discountRewardService = new Reward($discount);
            $this->applyDiscount($discount);
        }

        return $this;
    }


    private function applyDiscount(Discount $discountObject) {

        // first let's see which orders meet the filter criteria for a discount, then worry about the rewards :E
        if ($filter = $discountObject->getFilterBy()) {

            // looping through like this so the filtered array is there if needed
            if (is_array($filter)) $filterOrders = array_filter($this->orders, function($order) use ($discountObject) {

                // if there are problems getting necessary product/customer data, return false
                if (!$this->discountFilterService->addItemData($order)) {
                    return false;
                }

                // loop and call relavent method
                foreach ($discountObject->getFilterBy() as $filterKey => $filterType):
                    // if you specify without any filters
                    if (!count($filterType)) return false;

                    if (is_array($filterType)):

                        // lifetime spend
                        if ($filterKey == 'lifetimeSpend'):
                            if (!$this->discountFilterService->lifetimeSpend($filterType)):
                                return false;
                            endif;

                        // order
                        elseif ($filterKey == 'order'):
                            if (!$this->discountFilterService->order($filterType)):
                                return false;
                            endif;

                        // product
                        elseif ($filterKey == 'product'):
                            $this->discountFilterService->product($filterType);

                        // category
                        elseif ($filterKey == 'category'):
                            $this->discountFilterService->category($filterType);
                        else:
                            // no valid filter type
                            return false;
                        endif;

                    endif;
                endforeach;


                // final filtering of valid products
                $goodItems = $this->discountFilterService->filterValidProducts();

                // validate the product sum, now that we have a list of valid products
                if (isset($discountObject->getFilterBy()['product']['productSum'])) {
                    if (($productSum = $discountObject->getFilterBy()['product']['productSum']) !== null) {
                        if (!$this->discountFilterService->validProductSum($productSum, count($goodItems))) {
                            return false;
                        }
                    }
                }

                //compare with count of orders->items
                if (!count($goodItems)) {
                    if (!$this->discountFilterService->validOptions()) {
                        return false;
                    }
                }

                // invalidate last of questionable items
                if (!$this->discountFilterService->orderStatus() || !$this->discountFilterService->lifetimeSpendStatus()) {
                    return false;
                }

                // the discount is good for this order, apply the reward
                if ($rewardType = $discountObject->getRewardType()) {
                    if (method_exists($this->discountRewardService, $rewardType)) {
                        $this->discountRewardService->{$rewardType}($order, $goodItems);
                    }
                }

                return true;

            });

        }

        else {
          
            // no filters. the minimum you can do is specify order.itemSum > 0, or something

        }

    }


}