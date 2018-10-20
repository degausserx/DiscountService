<?php

namespace App\DataModels;

use App\DataModels\Customer;
use App\DataModels\Product;

class Order extends DataModel {
    protected $required = array('id', 'items', 'total', 'customerid');

    public $id;
    public $items;
    public $total;
    public $customerid;
}