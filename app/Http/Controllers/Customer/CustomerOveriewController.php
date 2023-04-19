<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CustomerService;

class CustomerOveriewController extends Controller
{
    public function customer360($acctionNo, $dss){

        try{

            $changeAccountNumber = StringHelper::formatAccountNumber($acctionNo);

            $customer = (new CustomerService)->customer360($changeAccountNumber);

            return $this->sendSuccess($customer, "Customer 360 Loaded", Response::HTTP_OK);
            
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }
    }
}
