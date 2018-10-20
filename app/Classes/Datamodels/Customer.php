<?php

namespace App\DataModels;

class Customer extends DataModel {
    protected $required = array('id', 'name', 'since', 'revenue');

    public $id;
    public $name;
    public $since;
    public $revenue;
}