<?php

namespace App\Contracts\Repositories;

use App\Datamodels\Customer;

interface CustomerRepositoryContract {

    public function make(Array $customer);

    public function get(Int $int);

    public function getAll();

    public function add(Customer $customer);

    public function findById($x);

}