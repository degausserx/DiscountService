<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\DataModels\OrderDataModel;
use App\Containers\DiscountContainerInterface;
use Redirect;

class DiscountsController extends Controller {

    // self injections
    protected $discountContainer;

    // constructor
    public function __construct(DiscountContainerInterface $discountContainer) {
        $this->discountContainer = $discountContainer;
    }


    // return upload file page
    public function upload() {
        return view('components.upload_file');
    }


    // showresult - process and display discounted orders
    public function showResult(OrderRequest $request) {

        $validated = $request->validated()['json_files'];
        $discountContainer = $this->discountContainer::make();

        // We could inject all our discounts here, but i think it's better to delegate that responsibility
        // DiscountHookLoader->load is sort of the built in resource used, but it could be very scalable with something like
        // $discountContainer->hook(DiscountHookLoader::Class, 'load'), or by independently injecting the functions;

        // add orders to discount service
        foreach ($validated as $file) {
            if ($array = json_decode(file_get_contents($file->getRealPath()), true)) {
                if ($order = new OrderDataModel($array)) $discountContainer->add($order);
            }
        }
        
        // response
        if (count($discountContainer)) {
            $return = $discountContainer->applyDiscounts()->get();
            return response()->json($return);
        }

        return Redirect::back()->withErrors('Invalid JSON file or data');
    }

}
