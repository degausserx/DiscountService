<?php

namespace App\Containers;

interface DiscountContainerInterface {

    // get json of users / products
    public function getJson();

    // create underlying classes, build service
    public function buildService();
        
    // replace discount classes being used
    public function setDiscounts();
}