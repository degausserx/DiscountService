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

            // not used with discounts yet, but ready to integrate
            $limit = $this->discount->getLimit();

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
        
        usort($items, function($a, $b) {
            if ($a['unit-price'] == $b['unit-price']) return 0;
            return ($a['unit-price'] < $b['unit-price']) ? -1 : 1;
        });

        foreach ($items as &$item) {
            if (in_array($item['product-id'], $data)) {
                $minus = $this->getDiscount($item[$property], $reward);
                $item[$property] -= $minus;
                $item[$property] = number_format($item[$property], 2);
                $order->total = $order->total - $minus;
                break;
            }
        }
    }

    // extra items
    public function item(Order $order, Array $data) {
        if ($number = $this->discount->getRewardNumber()) {
            
            $applyRewardTo = 'productLine';

            $order->discounts[] = $this->discount->getDescription();
            $each = $this->discount->getEach();
            if (isset($each['totalItems'])) $totalItems = $each['totalItems'];
            if (isset($each['totalSpent'])) $totalSpent = $each['totalSpent'];
            $limit = ($this->discount->getLimit() !== null) ? $this->discount->getLimit() : 1;

            foreach ($order->items as &$item) {
                if (in_array($item['product-id'], $data)) {
                    
                    if ($totalItems) {
                        $quantity = $item['quantity'];
                        $addedItems = floor($quantity / $totalItems);
                    }

                    elseif ($totalSpent) {
                        $totalPrice = $item['total'];
                        $addedItems = floor($totalPrice / $totalSpent);
                    }
  
                    // the number of times this can be applied
                    if ($addedItems > $limit && $limit > 0) $addedItems = $limit;
                    if ($totalItems) {
                        $item['quantity'] += $addedItems;
                        $item['quantity'] = strval($item['quantity']);
                    }

                }
            }

        }
    }

    public function reduction(Order $order, Array $data) {

    }

}