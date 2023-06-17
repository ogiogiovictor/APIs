<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer\CustomerAuthModel;
use App\Services\CustomerService;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\CustomerResource;

class GetCustomerController extends BaseApiController
{
    public function getCustomerDetails(Request $request) {
    
        $Auth = (new CustomerService)->getHeaderRequest($request)->toArray();

        if($Auth){
            $customerDetails = (new CustomerService)->customerDetails($Auth[0]['accountno']);
            return $this->sendSuccess(new CustomerResource($customerDetails), "Users Loaded", Response::HTTP_OK);
        }

        return $this->sendError("Error", "Error Loading User Data", Response::HTTP_UNAUTHORIZED);

    }
}
