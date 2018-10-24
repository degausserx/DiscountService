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

            $limit = $this->discount->getLimit();
            $i = 0;

            if ($applyRewardTo == 'order') {
                $order->total -= $this->getDiscount($order->total, $number);
                $order->total = number_format($order->total, 2);
            }

            elseif ($applyRewardTo == 'productLine') {
                foreach ($order->items as &$item) {
                    if (in_array($item['product-id'], $data)) {
                        $item['total'] -= $this->getDiscount($item['total'], $number);
                        $item['total'] = number_format($item['total'], 2);
                    }
                }
            }

            elseif ($applyRewardTo == 'cheapestItem') {
                $items = $order->items;

                usort($items, function($a, $b) {
                    if ($a['unit-price'] == $b['unit-price']) return 0;
                    return ($a['unit-price'] < $b['unit-price']) ? -1 : 1;
                });

                foreach ($items as &$item) {
                    if (in_array($item['product-id'], $data)) {
                        $item['unit-price'] -= $this->getDiscount($item['unit-price'], $number);
                        $item['unit-price'] = number_format($item['unit-price'], 2);
                        break;
                    }
                }
            }

            elseif ($applyRewardTo == 'cheapestProduct') {
                $items = &$order->items;

                usort($items, function($a, $b) {
                    if ($a['unit-price'] == $b['unit-price']) return 0;
                    return ($a['unit-price'] < $b['unit-price']) ? -1 : 1;
                });

                foreach ($items as &$item) {
                    if (in_array($item['product-id'], $data)) {
                        $item['total'] -= $this->getDiscount($item['total'], $number);
                        $item['total'] = number_format($item['total'], 2);
                        break;
                    }
                }

            }

            $order->discounts[] = $this->discount->getDescription();
        }

        $order->total = strval($order->total);
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