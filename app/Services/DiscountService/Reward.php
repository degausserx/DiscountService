<?php

namespace App\Services\DiscountService;

use App\Objects\Discounts\Discount;
use App\Datamodels\Order;

class Reward {

    private $discount;

    public function __construct(Discount $discount) {
        $this->discount = $discount;
    }

    private function getDiscount($num, $discount) {
        return number_format(((Float) $num * ($discount / 100)), 2);
    }

    // discounts *limit not yet integrated with discounts, would only apply to the productline though*
    public function discount(Order $order, Array $data) {
        if ($number = $this->discount->getRewardNumber()) {

            // discounts can be applied anywhere. we'll default to the order
            $applyRewardTo = $this->discount->getApplyRewardTo() ?? 'order';

            if ($applyRewardTo == 'order') {
                $this->processOrder($order, $number);
            }

            elseif ($applyRewardTo == 'productLine') {
                $this->processProductLine($order, $data, $number);
            }

            elseif ($applyRewardTo == 'cheapestItem') {
                $this->processCheapestItem($order, $data, $number, 'unit-price');
            }

            elseif ($applyRewardTo == 'cheapestProduct') {
                $this->processCheapestItem($order, $data, $number, 'total');
            }

            // reformat the number to a string, so that it matches the rest of the data
            $order->total = number_format($order->total, 2);

            // add to discount array
            $order->discounts[] = $this->discount->getDescription();
        }

        $order->total = strval($order->total);
    }

    // apply discount to order
    public function processOrder(Order $order, $reward) {
        $minus = $this->getDiscount($order->total, $reward);
        $order->total -= $minus;
    }

    // apply discount to product line
    public function processProductLine(Order $order, $reward) {
        foreach ($order->items as &$item) {
            if (in_array($item['product-id'], $data)) {
                $minus = $this->getDiscount($item['total'], $reward);
                $item['total'] -= $minus;
                $item['total'] = number_format($item['total'], 2);
                $order->total = $order->total - $minus;
            }
        }
    }

    // apply discount to cheapest item or
    // apply discount to productline of cheapest item
    public function processCheapestItem(Order $order, Array $data, $reward, $property) {
        $items = &$order->items;
        
        // sort from cheapest to most expensive
        usort($items, function($a, $b) use ($property) {
            if ($a[$property] == $b[$property]) return 0;
            return ($a[$property] < $b[$property]) ? -1 : 1;
        });

        // for each product in the order
        foreach ($items as &$item) {
            if (in_array($item['product-id'], $data)) {
                $minus = $this->getDiscount($item[$property], $reward);

                // modify price of product
                $item['total'] -= $minus;
                $item['total'] = number_format($item['total'], 2);

                // apply to order total
                $order->total = $order->total - $minus;
                break;
            }
        }
    }

    // extra items
    public function item(Order $order, Array $data) {
        if ($number = $this->discount->getRewardNumber()) {
            
            // only supported value of 'applyTo' for item rewards at this moment
            $applyRewardTo = 'productLine';
            $added = false;

            $each = $this->discount->getEach();
            if (isset($each['totalItems'])) $totalItems = $each['totalItems'];
            if (isset($each['totalSpent'])) $totalSpent = $each['totalSpent'];
            $limit = ($this->discount->getLimit() !== null) ? $this->discount->getLimit() : 1;

            foreach ($order->items as &$item) {
                if (in_array($item['product-id'], $data)) {
                    
                    if (isset($totalItems)) {
                        $quantity = $item['quantity'];
                        $addedItems = floor(($quantity / $totalItems) * $number);
                    }

                    elseif (isset($totalSpent)) {
                        $totalPrice = $item['total'];
                        $addedItems = floor(($quantity / $totalSpent) * $number);
                    }

                    // no 'each' data supplied
                    else {
                        $addedItems = $number;
                    }
  
                    // the number of times this can be applied
                    if ($addedItems > $limit && $limit > 0) $addedItems = $limit;

                    $item['quantity'] += $addedItems;
                    $item['quantity'] = strval($item['quantity']);
                    if ($addedItems > 0) $added = true;

                }
            }

            if ($added) $order->discounts[] = $this->discount->getDescription();

        }
    }

    public function reduction(Order $order, Array $data) {

    }

}