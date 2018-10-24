<?php

namespace App\Repositories;

use App\Contracts\Repositories\CustomerRepositoryContract;
use App\DataModels\Customer;
use Countable;
use Storage;

class CustomerRepository implements CustomerRepositoryContract, Countable {

    private $customers;

    public function __construct() {
        $this->setSource();
    }

    public function count() {
        return count($this->customers);
    }

    private function setSource() {
        $this->customers = json_decode(Storage::disk('local')->get('customers.json'), true);
        $this->customers = Customer::makeGroup($this->customers);
    }

    public function add(Customer $customer) {
        $this->customers[] = $customer;
    }
    
    public function make(Array $customer) {
        return Customer::make($customer);
    }

    public function get(Int $int) {
        if ($int < 1) return $this->getAll();
        return (isset($this->customers[$int])) ? $this->customers[$int] : null;
    }

    public function getAll() {
        return $this->customers;
    }

    public function findById($x) {
        return array_filter($this->customers, function($customer) use ($x) {
            return $customer->id == $x;
        });
    }

}