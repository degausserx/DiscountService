<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Contracts\DiscountServiceContainerContract;
use App\HookLoaders\DiscountHookLoader;
use App\DataModels\Order;
use Redirect;

class DiscountsController extends Controller {

    // self injections
    protected $discountContainer;

    // constructor
    public function __construct(DiscountServiceContainerContract $discountContainer) {
        $this->discountContainer = $discountContainer;
    }

    // return upload file page
    public function upload() {
        return view('components.upload_file');
    }

    // showresult - process and display discounted orders
    public function showResult(OrderRequest $request) {

        // get the validated json files
        $validated = $request->validated()['json_files'];

        // send in an array of discounts if you'd like. this is the default as per DiscountService: 
        $this->discountContainer->setDiscounts((new DiscountHookLoader())->load());

        // add orders to discountservice container
        foreach ($validated as $file) {
            if ($order = json_decode(file_get_contents($file->getRealPath()), true)) {
                $this->discountContainer->addOrder(Order::make($order));
            }
        }

        // generate discounts
        $this->discountContainer->generate();

        // get processed orders
        $ordersWithDiscountApplied = $this->discountContainer->getOrders();

        // response
        if (!empty($ordersWithDiscountApplied)) return response()->json($ordersWithDiscountApplied);

        // no orders found
        return Redirect::back()->withErrors('Invalid data');

    }

}
