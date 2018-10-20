<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\DataModels\Order;
use App\Contracts\DiscountServiceContainerContract;
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

        // set the source for where discounts will be taken from.
        // atm it takes either a class method, an array of discounts or a single discount
        $this->discountContainer->setSource('moo');

        // add orders to discountservice container
        foreach ($validated as $file) {
            if ($array = json_decode(file_get_contents($file->getRealPath()), true)) {
                if ($order = new Order($array)) $this->discountContainer->addOrder($order);
            }
        }

        // response
        if (count($this->discountContainer)) return response()->json($this->discountContainer->make());

        // no orders found
        return Redirect::back()->withErrors('Invalid data');


    }

}
