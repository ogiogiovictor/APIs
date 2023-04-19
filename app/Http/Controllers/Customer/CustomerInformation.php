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

        $postpaid = DimensionCustomer::selectRaw('StatusCode, count(*) as total')
        ->where("AccountType", 'Postpaid')->whereIn("StatusCode", ['A', 'S', '1', '0'])->groupBy('StatusCode')->get();

        $prepaid = DimensionCustomer::selectRaw('StatusCode, count(*) as total')
        ->where("AccountType", 'Prepaid')->whereIn("StatusCode", ['0', '1'])->groupBy('StatusCode')->get();


        if($request->type == 'Postpaid'){

            $customers = (new CustomerService)->getPostpaid($request->type); //getPostpaid

            $data = [
                'customers' => $customers,
                'postpaid' => $postpaid,
                'prepaid' => $prepaid,
            ];

            return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

        } else if($request->type == 'Prepaid'){

            $customers = (new CustomerService)->getPrepaid($request->type); //getPrepaid

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
}
