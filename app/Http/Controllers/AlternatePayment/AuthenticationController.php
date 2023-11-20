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
use App\Models\Customer\CustomerAuthModel;
use App\Http\Resources\IBEDCPayResource;

use App\Models\ECMIPayment;



class AuthenticationController extends BaseApiController
{


    public function testEcmi() {

        // try{

        //     $customers = ECMIPayment::paginate(10);
        //     return $this->sendSuccess($customers, "Customer Successfully Loaded", Response::HTTP_OK);

        // }catch(\Exception $e){

        //     return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);

        // }
        
       
    }

    public function login(Request $request){

        $meterNo = $request->meter_no;
        $accountType = $request->account_type;

        if($request->expectsJson()) {

            if(!$meterNo || !$accountType){
                return $this->sendError('Error', "Please Enter Meter/Account Type", Response::HTTP_BAD_REQUEST);
            }

            try{
                
               $customers = (new CustomerService)->authenticateCustomers($meterNo, $accountType);

                if(!$customers){
                    return $this->sendError("Error", "No Customer Results Found", Response::HTTP_BAD_REQUEST);
                }
               // $success['Authorizations'] = hash('sha256', $plainTextToken = Str::random(80));
                $success['customer'] = new IBEDCPayResource($customers);

                //Insert Authorization Headers
               $insertAuthorisation =  CustomerAuthModel::create([
                       // 'Authorization' => $success['Authorizations'],
                        'accountno' => $meterNo,
                        'expires_at' => date('Y-m-d H:m:s'),
                ]);

                
                  // Generate a personal access token for the customer
                  $token = $insertAuthorisation->createToken('customer-token');
                  // Return the token and customer details in the response
                  $success['Authorization'] = $token->plainTextToken;
                  CustomerAuthModel::where("id", $insertAuthorisation->id)->update([
                    'Authorization' => $success['Authorization'],
                  ]);
                


                return $this->sendSuccess($success, "Customer Successfully Loaded", Response::HTTP_OK);

            }catch(\Exception $e){
                return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
            }


        }else {
            return $this->sendError("Error", "Error Loading Data, Something went wrong(NOT JSON())", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
