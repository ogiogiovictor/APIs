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
        $response = Http::post('http://localhost:8001/api/v1/post_customer_crmd', $request->all());

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


    public function getCrmd() {

        $response = Http::get('http://localhost:8001/api/v1/get_customers');
        $data = $response->json();

        return $this->sendSuccess($data, "CRMD Loaded", Response::HTTP_OK);

    }


    public function updateStatus(Request $request){
        
        // $array =  $request->all();
         //$array['userid'] = 1; //this will be the person logged in
      
         try{
 
             $response = Http::post('http://localhost:8001/api/v1/update_crmd_doc', $request->all());
            
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
 


}
