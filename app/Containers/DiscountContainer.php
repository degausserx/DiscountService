<?php

namespace App\Containers;

use App\Containers\DiscountContainerInterface;
use App\Services\DiscountService;
use App\DataModels\ProductDataModel;
use App\DataModels\CustomerDataModel;
use App\Objects\Discounts\Discount;
use App\Builders\DiscountBuilder;
use App\HookLoaders\DiscountHookLoader;
use Storage;
use Config;

// return an instance of DiscountService
class DiscountContainer implements DiscountContainerInterface {

    protected $service;

    // saved resources
    protected $discounts;
    protected $customer;
    protected $product;

    // TODO: i dont know why i made this a singleton. 
    private static $instance = null;

    // make a new discount container
    static function make() {

        if (self::$instance == null) {
            self::$instance = new DiscountContainer();
            self::$instance->getJson();
            self::$instance->setDiscounts();
            self::$instance->buildService();
        }

        else self::$instance->buildService();

        return self::$instance->service;
    }

    // access to service without rebuilding
    static function get() {
        if (self::$instance == null) return self::build();
        return self::$instance->service;
    }

    // these are resources, and maybe come back to these with a more scalable method of getting them
    // TODO: let's actually try using a repository of some sort.
    public function getJson() {
        $this->customer = json_decode(Storage::disk('local')->get('customers.json'), true);
        $this->product = json_decode(Storage::disk('local')->get('products.json'), true);
    }
 
    public function buildService() {

        $customerDataModels = CustomerDataModel::generateGroup($this->customer, CustomerDataModel::class);
        $productDataModels = ProductDataModel::generateGroup($this->product, ProductDataModel::class);

        // create the service
        $this->service = new DiscountService(array(
            'discounts' => $this->discounts,
            'customerdata' => $customerDataModels,
            'productdata' => $productDataModels
        ));
    }

    // replace discount classes being used
    // TODO make compatable with array, single discount with or without hookloader
    public function setDiscounts() {
        $discounts = (new DiscountHookLoader())->load();
        $this->discounts = array();
        foreach ($discounts as $discount) {
            $this->discounts[] = $discount;
        }
    }

}