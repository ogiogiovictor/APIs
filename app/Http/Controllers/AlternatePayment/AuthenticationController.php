<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Models\DimensionCustomer;
use App\Services\CustomerService;
use App\Http\Resources\NewResource;
use Illuminate\Support\Str;


class AuthenticationController extends BaseApiController
{
    public function login(Request $request){

        $meterNo = $request->meter_no;
        $accountType = $request->account_type;

        if($request->expectsJson()) {

            if(!$meterNo || !$accountType){
                return $this->sendError('Error', "Please Enter Meter/Account Type", Response::HTTP_BAD_REQUEST);
            }

            try{

                $customers = (new CustomerService)->authenticateCustomers($meterNo, $accountType);
               // $success['Authorization'] = $customers->createToken('API Token')->plainTextToken;
                $success['Authorization'] = hash('sha256', $plainTextToken = Str::random(80));
                $success['customer'] = new NewResource($customers);


                return $this->sendSuccess($success, "Customer Successfully Loaded", Response::HTTP_OK);

            }catch(\Exception $e){
                return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
            }


        }else {
            return $this->sendError("Error", "Error Loading Data, Something went wrong(NOT JSON())", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
