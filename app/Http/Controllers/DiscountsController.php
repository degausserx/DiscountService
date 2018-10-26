<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
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
        // send in an array of discounts
        $this->discountContainer->setDiscounts((new DiscountHookLoader())->load());
    }

    // return upload file page
    public function upload() {
        return view('layouts.upload_file');
    }

    // showresult - process and display discounted orders
    public function showResult(OrderRequest $request) {

        // get the validated json files
        $validated = $request->validated()['json_files'];

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

        // clear orders in discount container
        // $this->discountContainer->clearOrders();

        // clear discounts in discounts container
        // $this->discountContainer->clearDiscounts();

        // response
        if (!empty($ordersWithDiscountApplied)) return response()->json($ordersWithDiscountApplied);

        // no orders found
        return Redirect::back()->withErrors('Invalid data');

    }

    public function applyDiscount(Request $request) {
        $data = $request->all();

        $this->discountContainer->addOrder(Order::make($data));
        $this->discountContainer->generate();

        return response()->json($this->discountContainer->getOrders());
    }

}
