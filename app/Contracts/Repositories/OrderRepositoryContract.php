<?php

namespace App\Contracts\Repositories;

use App\Datamodels\Order;

interface OrderRepositoryContract {

    public function make(Array $order);

    public function get(Int $int);

    public function getAll();

    public function add(Order $order);
    
    public function findById($x);

}