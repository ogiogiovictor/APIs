<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use App\Helpers\StringHelper;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CRMDCustomers;
use Illuminate\Support\Facades\Log;
use App\Models\DimensionCustomer;
use Illuminate\Support\Facades\Auth;
use App\Services\GeneralService;
use App\Http\Resources\CustomerCRMDResource;
use App\Models\CRMDHistory;

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


    public function crmdStore(Request $request){

        //if($request->expectsJson()) {
            $validatedData = $request->validate([
                'AccountNo' => 'required|string',
                //'MeterNo' => 'required',
                'AcountType' => 'required',
                'Old_FullName' => 'required',
                'New_FullName' => 'required',
            ]);


            if($request->id){
                   
                $addData = CRMDCustomers::where('id', $request->id)->update([
                    'DateAdded' => date('Y-m-d H:i:s'),
                    'AccountNo' => $request->AccountNo,
                    'MeterNo' => $request->MeterNo,
                    'AcountType' => $request->AcountType,
                    'Old_FullName' => $request->Old_FullName,
                    'New_FullName' => $request->New_FullName,
                    'Address' => $request->Address,
                    'DistributionID' => $request->DistributionID,
                    'hub' => DimensionCustomer::where('AccountNo', $request->AccountNo)->first()->BusinessHub,
                    'region' => DimensionCustomer::where('AccountNo', $request->AccountNo)->first()->Region,
                    'service_center' => DimensionCustomer::where('AccountNo', $request->AccountNo)->value("service_center"),
                    'userid' => Auth::user()->id,
                    'new_firstname' => $request->new_firstname,
                    'new_surname' => $request->new_surname,
                    'new_address' => $request->new_address,
                    'mobile' => $request->mobile,
                ]);
    
                return $this->sendSuccess($addData, "Customer 360 Loaded", Response::HTTP_OK);
            }else {
                $addData = CRMDCustomers::create([
                    'DateAdded' => date('Y-m-d H:i:s'),
                    'AccountNo' => $request->AccountNo,
                    'MeterNo' => $request->MeterNo,
                    'AcountType' => $request->AcountType,
                    'Old_FullName' => $request->Old_FullName,
                    'New_FullName' => $request->New_FullName,
                    'Address' => $request->Address,
                    'DistributionID' => $request->DistributionID,
                    'hub' => DimensionCustomer::where('AccountNo', $request->AccountNo)->first()->BusinessHub,
                    'region' => DimensionCustomer::where('AccountNo', $request->AccountNo)->first()->Region,
                    'service_center' => DimensionCustomer::where('AccountNo', $request->AccountNo)->value("service_center"),
                    'userid' => Auth::user()->id,
                    'new_firstname' => $request->new_firstname,
                    'new_surname' => $request->new_surname,
                    'new_address' => $request->new_address,
                    'mobile' => $request->mobile,
                ]);
    
                return $this->sendSuccess($addData, "Customer 360 Loaded", Response::HTTP_OK);

            }

        
           
            
        // }else {
        //     Log::info('Request Payload', $request->all());
        //     return $this->sendError("Error Loading Data, Something went wrong",  $request->all(),  Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }


    public function getCustomers(){

        $user = Auth::user();
        //$getSpecialRole =  (new GeneralService)->getSpecialRole();
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();

       // $customers = CRMDCustomers::all();
       $customers = CRMDCustomers::query()->latest()
         ->when($user->isRegion(), function ($query) use ($getUserRoleObject) {
              return $query->where('region', $getUserRoleObject['region']);
            })
        ->when($user->isBhub(), function ($query) use ($getUserRoleObject) {
            return $query->where('region', $getUserRoleObject['region'])->where('hub', $getUserRoleObject['business_hub']);
            })
        ->when($user->isSCenter(), function ($query) use ($getUserRoleObject) {
            return $query->where('region', $getUserRoleObject['region'])->where('hub', $getUserRoleObject['business_hub'])
            ->where('service_center', $getSpecigetUserRoleObjectalRole['service_center']);
            })
         ->when($user->isHQ(), function ($query) use ($getUserRoleObject, $user) {
            return $query;
            },
            function ($query) {
                // Handle the default case when none of the conditions pass
                // You can add conditions or actions for the default case here
                return $query->where('user_id', '=', $user->id);
            })
            ->get();

        return $this->sendSuccess(CustomerCRMDResource::collection($customers), "Customer 360 Loaded", Response::HTTP_OK);
    }


    public function processTransaction(Request $request) {

        $user = Auth::user();
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();

        try {
            $customers = CRMDCustomers::query()->find($request->id);

            if (!$customers) {
                return $this->sendError("No Result Found", Response::HTTP_BAD_REQUEST);
            }
        
            $updateData = [];
        
            // if ($request->approval_type == "Pending" && $request->hub == $getUserRoleObject['business_hub'] && $getUserRoleObject['role'] == 'teamlead') {
            //     $updateData = ['approval_type' => 1, 'confirmed_by' => Auth::user()->id];
            // } elseif ($request->approval_type == "Reviewed By TL" && $request->hub == $getUserRoleObject['business_hub'] && $getUserRoleObject['role'] == 'businesshub_manager') {
            //     $updateData = ['approval_type' => 2, 'confirmed_by' => Auth::user()->id];
            // } elseif ($request->approval_type == "Approved By BHM" && $request->hub == $getUserRoleObject['business_hub'] && $getUserRoleObject['role'] == 'audit') {
            //     $updateData = ['approval_type' => 3, 'confirmed_by' => Auth::user()->id];
            // } elseif ($request->approval_type == "Approved By Audit" && $request->hub == $getUserRoleObject['business_hub'] && $getUserRoleObject['role'] == 'billing') {
            //     $updateData = ['approval_type' => 4, 'confirmed_by' => Auth::user()->id];
                  //Run a script to update the customer table in the live Environment.
            // }

            //You should replace this section of the code with the work at the tope
            if ($request->approval_type == "Pending") {
                $updateData = ['approval_type' => 1, 'confirmed_by' => Auth::user()->id];
            } elseif ($request->approval_type == "Reviewed By TL") {
                $updateData = ['approval_type' => 2, 'confirmed_by' => Auth::user()->id];
            } elseif ($request->approval_type == "Approved By BHM") {
                $updateData = ['approval_type' => 3, 'confirmed_by' => Auth::user()->id];
            } elseif ($request->approval_type == "Approved By Audit") {
                $updateData = ['approval_type' => 4, 'confirmed_by' => Auth::user()->id];
                //Run a script to update the customer table in the live Environment.
            }
        
            if (!empty($updateData)) {
                $customers->update($updateData);
            } else {
                return $this->sendError("Invalid request parameters", Response::HTTP_BAD_REQUEST);
            }
        
            $addData = CRMDHistory::create([
                'user_id' => Auth::user()->id,
                'crmd_id' => $request->id,
                'status' => $request->status,
                'approval' => $request->approval_type,
                'comment' => $request->comment,
            ]);
        return $this->sendSuccess($addData, "Customer 360 Loaded", Response::HTTP_OK);

        }catch(\Exception $e){
            return $this->sendError($e->getMessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }

        
    }


    public function rejectTransaction(Request $request){

        try{

            $user = Auth::user();
            $getUserRoleObject = (new GeneralService)->getUserLevelRole();

            // $customers = CRMDCustomers::where('id', $request->id)->update([
            //     'approval_type' => 5,
            //     'confirmed_by' => Auth::user()->id,
            // ]);

        $customers = CRMDCustomers::query()
            ->where('id', $request->id) // Find the record by ID
            ->when(
                !$request->approval_type || (!in_array($request->approval_type, [3, 4]) && $request->hub == $getUserRoleObject['business_hub'] && $getUserRoleObject['role'] == 'teamlead'),
                function ($query) use ($request) {
                    return $query->update([
                        'approval_type' => $request->approval_type,
                        'confirmed_by' => Auth::user()->id,
                    ]);
                }
            );

        if ($customers) {
            $addData = CRMDHistory::create([
                'user_id' => Auth::user()->id,
                'crmd_id' => $request->id,
                'status' => $request->status,
                'approval' => $request->approval_type,
                'comment' => $request->comment,
            ]);
            return $this->sendSuccess($addData, "Customer Information Successfully Rejected", Response::HTTP_OK);
        }else {
            return $this->sendError("You do not have permission to update this request.", Response::HTTP_BAD_REQUEST);
        }

       


        }catch(\Exception $e){
            return $this->sendError($e->getMessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }
        

    }


    public function getPendingCustomers(){

        $user = Auth::user();
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();

        try {

            $customers = CRMDCustomers::query()->latest()
            ->when($getUserRoleObject['role'] == 'teamlead', function ($query) use ($getUserRoleObject) {
                return $query->where([ 'approval_type' => 0])->where('hub', $getUserRoleObject['business_hub']);
            })
            ->when($getUserRoleObject['role'] == 'businesshub_manager', function ($query) use ($getUserRoleObject) {
                return $query->where(['approval_type' => 1])->where('hub', $getUserRoleObject['business_hub']);
            })
            ->when($getUserRoleObject['role'] == 'audit', function ($query) use ($getUserRoleObject) {
                return $query->where(['approval_type' => 2 ])->where('hub', $getUserRoleObject['business_hub']);
            })
            ->when($getUserRoleObject['role'] == 'billing', function ($query) use ($getUserRoleObject) {
                return $query->where(['approval_type' => 3 ])->where('hub', $getUserRoleObject['business_hub']);
            })
            ->when($getUserRoleObject['role'] == 'admin', function ($query) use ($getUserRoleObject) {
                return $query;
            },
            function ($query) {
                return $query->where('userid', '=', $user->id);
            })->get();

        return $this->sendSuccess(CustomerCRMDResource::collection($customers), "Record Successsfully Loaded", Response::HTTP_OK);

        }catch(\Exception $e){
            return $this->sendError($e->getMessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }


    }


}
