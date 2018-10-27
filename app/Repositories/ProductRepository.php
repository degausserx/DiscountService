<?php

namespace App\Repositories;

use App\Repositories\JsonRepository;
use App\Contracts\Repositories\ProductRepositoryContract;
use App\DataModels\Product;

class ProductRepository extends JsonRepository implements ProductRepositoryContract {

    public function __construct() {
        $this->setSource('products', function($data) {
            return Product::makeGroup($data);
        });
    }

    public function findById($x) {
        return array_filter($this->data, function($product) use ($x) {
            return $product->id == $x;
        });
    }

}