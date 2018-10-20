<?php

namespace App\Contracts\Repositories;

use App\Datamodels\Product;

interface ProductRepositoryContract {

    public function make(Array $product);

    public function get(Int $int);

    public function getAll();

    public function add(Product $product);
    
    public function findById($x);

}