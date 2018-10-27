<?php

namespace App\Repositories;

use App\Repositories\JsonRepository;
use App\Contracts\Repositories\CustomerRepositoryContract;
use App\DataModels\Customer;

class CustomerRepository extends JsonRepository implements CustomerRepositoryContract {

    public function __construct() {
        $this->setSource('customers', function($data) {
            return Customer::makeGroup($data);
        });
    }

}