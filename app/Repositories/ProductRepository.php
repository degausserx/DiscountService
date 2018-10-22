<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepositoryContract;
use App\DataModels\Product;
use Countable;
use Storage;

class ProductRepository implements ProductRepositoryContract, Countable {

    private $products = array();
    
    public function __construct() {
        $this->setSource();
    }

    public function count() {
        return count($this->products);
    }

    private function setSource() {
        $this->products = json_decode(\Storage::disk('local')->get('products.json'), true);
        $this->products = Product::makeGroup($this->products);
    }

    public function add(Product $product) {
        $this->products[] = $product;
    }
    
    public function make(Array $product) {
        return Product::make($product);
    }

    public function get(Int $int) {
        if ($int < 1) return $this->getAll();
        return (isset($this->products[$int])) ? $this->products[$int] : null;
    }

    public function getAll() {
        return $this->products;
    }

    public function findById($x) {
        return array_filter($this->products, function($product) use ($x) {
            return $product->id == $x;
        });
    }

}