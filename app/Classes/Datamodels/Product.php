<?php

namespace App\DataModels;

class Product extends DataModel {
    protected $required = array('id', 'description', 'category', 'price');

    public $id;
    public $description;
    public $category;
    public $price;
}