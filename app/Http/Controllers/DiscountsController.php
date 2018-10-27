<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use Illuminate\Http\Request;
use App\Contracts\DiscountServiceContainerContract;
use App\HookLoaders\DiscountHookLoader;
use App\DataModels\Order;
use Redirect;
use Exception;

class DiscountsController extends Controller {

    // self injections
    protected $discountContainer;


    // constructor
    public function __construct(DiscountServiceContainerContract $discountContainer) {
        $this->discountContainer = $discountContainer;
        $this->discountContainer->setDiscounts((new DiscountHookLoader())->load());
    }


    // web - return upload file page
    public function upload() {
        return view('layouts.upload_file');
    }

    
    // web - showresult - process and display discounted orders
    public function showResult(OrderRequest $request) {

        // get the validated json files
        $validated = $request->validated()['json_files'];

        // add orders to discountservice container
        foreach ($validated as $file) {
            if ($order = json_decode(file_get_contents($file->getRealPath()), true)) {
                if (!$this->addOrder($order)) {
                    return response()->json(['success' => 0]);
                    die();
                }
            }
        }

        // generate discounts
        $this->discountContainer->generate();

        // get processed orders
        $ordersWithDiscountApplied = $this->discountContainer->getOrders();

        // $this->discountContainer->clearOrders();
        // $this->discountContainer->clearDiscounts();

        // response
        if (!empty($ordersWithDiscountApplied)) return response()->json($ordersWithDiscountApplied);

        // no orders found
        return Redirect::back()->withErrors('No orders found');

    }


    // api method
    public function applyDiscount(Request $request) {
        $data = $request->all();

        if ($this->addOrder($data)) {

            $this->discountContainer->generate();

            return response()->json($this->discountContainer->getOrders());

        } else {

            // request failed
            response()->json(['success' => 0]);

        }
    }


    // add order to discountcontainer method
    public function addOrder($data) {
        try {
            $this->discountContainer->addOrder(new Order($data));
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

}
