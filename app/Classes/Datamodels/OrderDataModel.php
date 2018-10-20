<?php

namespace App\DataModels;

use App\DataModels\CustomerDataModel;
use App\DataModels\ProductDataModel;

class OrderDataModel extends DataModel {
    protected $required = array('id', 'items', 'total', 'customerid');

    public $id;
    public $items;
    public $total;
    public $customerid;
}