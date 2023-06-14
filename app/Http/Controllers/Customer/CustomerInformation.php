<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use DB;
use App\Models\DimensionCustomer;
use App\Services\CustomerService;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\NewResource;
use App\Http\Resources\ZoneResource;
use App\Models\ZoneCustomer;
use App\Http\Requests\RecordRequest;
use Illuminate\Support\Facades\Http;
use App\Models\KCTGenerator;
use Illuminate\Support\Facades\Auth;
use App\Services\GeneralService;

class CustomerInformation extends BaseApiController
{
    public function index(){

        //To be removed later
        return $this->sendError("No Data", "No Access is given yet", Response::HTTP_BAD_REQUEST);
    
        $customers = (new CustomerService)->getCustomerInfo();

        if($customers){
          return $this->sendSuccess($customers, "Customer Successfully Loaded- ". count($customers), Response::HTTP_OK);
        }else {
         return $this->sendError("No Data", $errorMessages = [], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(CustomerRequest $request){

        //$search_term = trim($request->AccountNo);

        $search_term = $request->validated();

        if($search_term){
            try {

                $customers = (new CustomerService)->findCustomer($request->AccountNo);

                if(!empty($customers)) {
                    return $this->sendSuccess(NewResource::collection($customers), "Customer Successfully Loaded", Response::HTTP_OK);
                }else {
                    $result = ZoneCustomer::where("AccountNo", $request->AccountNo)->orWhere("MeterNo", $request->AccountNo);
                    return $this->sendSuccess(ZoneResource::collection($result), "Customer Successfully Loaded From Zone", Response::HTTP_OK);
                    
                }

            }catch(Exception $e){
                return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
            }
        }else {
            return $this->sendError("Message", "Provide Account/Meter No", Response::HTTP_BAD_REQUEST);
        }

    }



    public function allCustomers(Request $request){

        $user = Auth::user();
        $getSpecialRole =  (new GeneralService)->getSpecialRole();
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();
         
        if(in_array($getUserRoleObject['role'], $getSpecialRole) && $user->isHQ()){

            $postpaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = \'A\' THEN \'Active\'
                WHEN StatusCode = \'C\' THEN \'Close\'
                WHEN StatusCode = \'I\' THEN \'Inactive\'
                WHEN StatusCode = \'S\' THEN \'Suspended\'
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Postpaid')
            ->groupBy('StatusCode')
            ->get();

            $prepaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = \'1\' THEN \'Active\'
                WHEN StatusCode = \'0\' THEN \'Inactive\'
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Prepaid')
            ->groupBy('StatusCode')
            ->get();

        }else if($user->isRegion()){ 
            $postpaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = \'A\' THEN \'Active\'
                WHEN StatusCode = \'C\' THEN \'Close\'
                WHEN StatusCode = \'I\' THEN \'Inactive\'
                WHEN StatusCode = \'S\' THEN \'Suspended\'
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Postpaid')
            ->where("Region", $getUserRoleObject['region'])
            ->groupBy('StatusCode')
            ->get();

            $prepaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = \'1\' THEN \'Active\'
                WHEN StatusCode = \'0\' THEN \'Inactive\'
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Prepaid')
            ->where("Region", $getUserRoleObject['region'])
            ->groupBy('StatusCode')
            ->get();
        }else if($user->isBhub()){
            $postpaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = \'A\' THEN \'Active\'
                WHEN StatusCode = \'C\' THEN \'Close\'
                WHEN StatusCode = \'I\' THEN \'Inactive\'
                WHEN StatusCode = \'S\' THEN \'Suspended\'
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Postpaid')
            ->where("Region", $getUserRoleObject['region'])
            ->where("BusinessHub", $getUserRoleObject['business_hub'])->orWhere("BUID", $getUserRoleObject['business_hub'])
            ->groupBy('StatusCode')
            ->get();

            $prepaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = \'1\' THEN \'Active\'
                WHEN StatusCode = \'0\' THEN \'Inactive\'
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Prepaid')
            ->where("Region", $getUserRoleObject['region'])
            ->where("BusinessHub", $getUserRoleObject['business_hub'])->orWhere("BUID", $getUserRoleObject['business_hub'])
            ->groupBy('StatusCode')
            ->get();
        }else if($user->isSCenter()){ 
            $postpaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = \'A\' THEN \'Active\'
                WHEN StatusCode = \'C\' THEN \'Close\'
                WHEN StatusCode = \'I\' THEN \'Inactive\'
                WHEN StatusCode = \'S\' THEN \'Suspended\'
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Postpaid')
            ->where("Region", $getUserRoleObject['region'])
            ->where("BusinessHub", $getUserRoleObject['business_hub'])
            ->where("service_center", $getUserRoleObject['sc'])
            ->groupBy('StatusCode')
            ->get();

            $prepaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = \'1\' THEN \'Active\'
                WHEN StatusCode = \'0\' THEN \'Inactive\'
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Prepaid')
            ->where("Region", $getUserRoleObject['region'])
            ->where("BusinessHub", $getUserRoleObject['business_hub'])
            ->where("service_center", $getUserRoleObject['sc'])
            ->groupBy('StatusCode')
            ->get();
        }




        if($request->type == 'Postpaid'){

            $customers = (new CustomerService)->getPostpaid($request); //getPostpaid

            $data = [
                'customers' => $customers,
                'postpaid' => $postpaid,
                'prepaid' => $prepaid,
            ];

            return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

        } else if($request->type == 'Prepaid'){

            $customers = (new CustomerService)->getPrepaid($request); //getPrepaid

            $data = [
                'customers' => $customers,
                'postpaid' => $postpaid,
                'prepaid' => $prepaid,
               ];

            return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

        }else {

            $customers = (new CustomerService)->getCustomerInfo(); //getAll

            $data = [
                'customers' => $customers,
                'postpaid' => $postpaid,
                'prepaid' => $prepaid,
               ];

            return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

        }

    }



    public function cstore(RecordRequest $request) {
        $baseUrl = env('CUSTOMER_API_URL');
        $addCustomerUrl = $baseUrl . 'post_customer_crmd';

        $response = Http::post($addCustomerUrl, $request->all());

        try{
            if($response['data'] == '201'){
                return $this->sendSuccess($response->json(), "Customer Created", Response::HTTP_OK);
            }else{
                return $this->sendError("Error", $response->json() , Response::HTTP_UNAUTHORIZED);
            }

        }catch(\Exception $err){
            return $this->sendError("Error", $err , Response::HTTP_UNAUTHORIZED);
        }
    }

    public function addNewCustomer(Request $request){
        return $request;
        
    }


    public function getCrmd() {

        try{

            $baseUrl = env('CUSTOMER_API_URL');

            $user = Auth::user();

            if($user->isHQ()){
                $addCustomerUrl = $baseUrl . 'get_customers/all';
            }else if($user->isBhub()){  //This will be for teamlead i need to implement this functionality for permission
                $addCustomerUrl = $baseUrl . 'get_customers/verify';
            }else if ($user->isSCenter()){ //This will be for business hub manager i need to implement this functionality for permission
                $addCustomerUrl = $baseUrl . 'get_customers/approve';
            }else {
                $addCustomerUrl = $baseUrl . 'get_customers';
            }
           

            $response = Http::get($addCustomerUrl);
            $data = $response->json();
    
            return $this->sendSuccess($data, "CRMD Loaded", Response::HTTP_OK);

        }catch(\Exception $e){
            return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }
       

    }


    public function updateStatus(Request $request){
        
        // $array =  $request->all();
         //$array['userid'] = 1; //this will be the person logged in
      
         try{

            $baseUrl = env('CUSTOMER_API_URL');
            $addCustomerUrl = $baseUrl . 'update_crmd_doc';
 
             $response = Http::post($addCustomerUrl, $request->all());
            
             if($response){
                 return $this->sendSuccess($response, "Customer CRMD Approved Successfully", Response::HTTP_OK);
             }
          
 
         }catch(\Exception $e){
             return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
         }
 
      
    }

     public function percentageOwed(){

        $customer = (new CustomerService)->getDisconnections();
        return $this->sendSuccess($customer, "All Customers To Disconnection", Response::HTTP_OK);
     }



     public function generatekct(Request $request) {

        $data = KCTGenerator::where("meter_number", $request->meter_number)->value("kct_code");

        if($data){
            return $this->sendSuccess($data, "KCT Value Successfully Generated", Response::HTTP_OK);
        } else  {
            return $this->sendError("Error", "No Record Found" , Response::HTTP_UNAUTHORIZED);
        }

        
     }



     public function addNewCustomerCRMD(Request $request){
       
        try{

            $baseUrl = env('CUSTOMER_API_URL');
            $addCustomerUrl = $baseUrl . 'add_customer';

            $response = Http::post($addCustomerUrl, $request->all());

            if ($response) {
                return $this->sendSuccess($response, "Customer Successfully Created", Response::HTTP_OK);
            }

        }catch(\Exception $e){
            return $e->getmessage();
        }
    }




    public function pendingCustomer(){

        $user = Auth::user();

        $baseUrl = env('CUSTOMER_API_URL');
        $addCustomerUrl = $baseUrl . 'pending_customers';

        try{

            if ($user->isHQ()) { 
                $filters = [
                    'status' => 'pending'
                ];
                $response = Http::get( $addCustomerUrl, $filters);
            } else if ($user->isRegion()) {
                $checkLevel = Auth::user()->level;
                $values = explode(", ", $checkLevel);
                $desiredValue = $values[0];
                $filters = [
                    'region' => $desiredValue,
                    'status' => 'pending'
                ];

                $response = Http::get( $addCustomerUrl, $filters);
            } else if ($user->isBhub()) {
                $checkLevel = Auth::user()->level;
                $values = explode(", ", $checkLevel);
                $desiredValue = $values[1];
                $filters = [
                    'business_hub' => $desiredValue,
                    'status' => 'pending'
                ];
                $response = Http::get( $addCustomerUrl, $filters);
            }  else if ($user->isSCenter()) {
                $checkLevel = Auth::user()->level;
                $values = explode(", ", $checkLevel);
                $desiredValue = $values[2];
                $filters = [
                    'service_center' =>  $filters,
                    'status' => 'pending'
                ];
                $response = Http::get( $addCustomerUrl, $filters);
            } 

            return $response;

        }catch(\Exception $e){
            return $e->getmessage();
        }
    }




    public function updateCustomer(Request $request){
      
        try{

           $baseUrl = env('CUSTOMER_API_URL');
           $addCustomerUrl = $baseUrl . 'update_customers_approve';

            $response = Http::post($addCustomerUrl, $request->all());
           
            if($response){
                return $this->sendSuccess($response, "Customer Approved Successfully", Response::HTTP_OK);
            }
         

        }catch(\Exception $e){
            return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }

    }

     
 


}
