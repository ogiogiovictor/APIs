<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use App\Helpers\StringHelper;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;

class CustomerOveriewController extends BaseApiController
{
    public function customer360($acctionNo, $dss, $accountType, $MeterNo){

        try{

            if($accountType == 'Postpaid'){
                $changeAccountNumber = StringHelper::formatAccountNumber($acctionNo);
            }else {
                $changeAccountNumber = $acctionNo;
            }

            $customer = (new CustomerService)->customer360($changeAccountNumber, $dss, $accountType, $MeterNo);

            return $this->sendSuccess($customer, "Customer 360 Loaded", Response::HTTP_OK);
            
        }catch(\Exception $e){
            return $this->sendError($e->getMessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }
    }
}
