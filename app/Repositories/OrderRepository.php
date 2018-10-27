<?php

namespace App\Repositories;

use App\DataModels\Order;
use App\Contracts\Repositories\OrderRepositoryContract;
use App\Repositories\JsonRepository;

class OrderRepository extends JsonRepository implements OrderRepositoryContract {

    public function __construct() {

    }

}