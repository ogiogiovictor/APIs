<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use App\Helpers\StringHelper;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CRMDCustomers;
//use App\Models\CRMDHistory;
use Illuminate\Support\Facades\Log;
use App\Models\DimensionCustomer;
use Illuminate\Support\Facades\Auth;
use App\Services\GeneralService;
use App\Http\Resources\CustomerCRMDResource;
use App\Models\CRMDHistory;
use DB;
use Carbon\Carbon;
use App\Models\BusinessUnit;
use App\Models\ZoneECMI;
use App\Models\ZoneCustomer;
use App\Models\EMSBusinessUnit;
use App\Models\ECMIBusinessUnit;
use App\Jobs\CRMDJOB;
use App\Models\User;


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

        //return $request;
        //return $this->sendSuccess(EMSBusinessUnit::where("BUID", ZoneCustomer::where('AccountNo', $request->AccountNo)->value("BUID"))->value("Name"),  "ERROR",  Response::HTTP_OK);
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();

        if($getUserRoleObject['role'] != 'cro'){
            return $this->sendError("No Access To Create CRMD",  "ERROR",  Response::HTTP_INTERNAL_SERVER_ERROR);
        }        
        
        //if($request->expectsJson()) {
            $validatedData = $request->validate([
                'AccountNo' => 'required|string',
                //'MeterNo' => 'required',
                'AcountType' => 'required',
                'Old_FullName' => 'required',
                'New_FullName' => 'required',
            ]);

            

            if($request->AcountType == "Postpaid"){

                $accountHub = strtoupper(EMSBusinessUnit::where("BUID", ZoneCustomer::where('AccountNo', $request->AccountNo)->value("BUID"))->value("Name"));
                $accountRegion =  strtoupper(EMSBusinessUnit::where("BUID", ZoneCustomer::where('AccountNo', $request->AccountNo)->value("BUID"))->value("State"));
            } 
            
            if($request->AcountType == "Prepaid"){
             
                $accountHub = strtoupper(ZoneECMI::where("MeterNo", $request->MeterNo)->value("BUID"));
                $accountRegion = strtoupper(ZoneECMI::where("MeterNo", $request->MeterNo)->value("State"));
            }

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
                    'hub' =>  $request->AcountType == "Postpaid" ? strtoupper(EMSBusinessUnit::where("BUID", ZoneCustomer::where('AccountNo', $request->AccountNo)->value("BUID"))->value("Name")) :
                    strtoupper(ZoneECMI::where("MeterNo", $request->MeterNo)->value("BUID")),
                    'region' => $request->AcountType == "Postpaid"   ?  strtoupper(EMSBusinessUnit::where("BUID", ZoneCustomer::where('AccountNo', $request->AccountNo)->value("BUID"))->value("State")) :
                    strtoupper(ZoneECMI::where("MeterNo", $request->MeterNo)->value("State")),
                    'service_center' => strtoupper(DimensionCustomer::where('AccountNo', $request->AccountNo)->value("service_center")),
                    'userid' => Auth::user()->id,
                    'new_firstname' => $request->new_firstname,
                    'new_surname' => $request->new_surname,
                    'new_address' => $request->new_address,
                    'mobile' => isset($request->mobile) ? $request->mobile : 'null',
                    'old_mobile' => $request->old_mobile,
                    'tarriffcode' => $request->TarriffCode,
                    'new_tarriff_code' => $request->new_tarriff,
                    'email' => $request->Email,
                    'new_email' => $request->new_email,
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
                    
                    'region' => strtoupper($request->Region),
                    'hub' => strtoupper($request->dBusinessHub),
                    'service_center' => strtoupper($request->dServiceCenter),


                    //'hub' => $accountHub,  //strtoupper(BusinessUnit::where("BUID", DimensionCustomer::where('AccountNo', $request->AccountNo)->first()->value("BUID")))->value("Name"),
                    //'region' =>  $accountRegion, //strtoupper(BusinessUnit::where("BUID", DimensionCustomer::where('AccountNo', $request->AccountNo)->first()->value("BUID")))->value("State"),
                    //'service_center' => strtoupper(DimensionCustomer::where('AccountNo', $request->AccountNo)->value("service_center")),
                    'userid' => Auth::user()->id,
                    'new_firstname' => $request->new_firstname,
                    'new_surname' => $request->new_surname,
                    'new_address' => $request->new_address,
                    'mobile' => isset($request->mobile) ? $request->mobile : 'null',
                    'old_mobile' => isset($request->old_mobile) ? $request->old_mobile : 'null',
                    'tarriffcode' => $request->TarriffCode,
                    'new_tarriff_code' => $request->new_tarriff,
                    'email' => isset($request->Email) ? $request->Email: 'null',
                    'new_email' => $request->new_email,
                ]);


                $roles = [
                    1 => 'teamlead',
                    2 => 'businesshub_manager',
                    3 => 'audit',
                    4 => 'billing',
                ];
                
                $targetRole = $roles[$addData->id] ?? null;
                
                if ($targetRole) {
                    $user = User::where("region", $this->process->region)
                        ->where("business_hub", $this->process->business_hub)
                        ->where("user_role", $targetRole)
                        ->first();
                
                    if ($user) {
                        $email = $user->email;
                        $name = $user->name;
                    }
                }

                

                $generateData = [
                    'email' =>  $email, //User::where("bhub", $accountHub)->value("email"),
                    'id' =>  $addData->id,
                    'accountNo' => $request->AccountNo,
                    'meterNo' => $request->MeterNo,
                    'name' => $request->New_FullName,
                ];

                dispatch(new CRMDJOB($generateData))->delay(5);
                
    
                return $this->sendSuccess($addData, "Customer 360 Loaded", Response::HTTP_OK);

            }

        
           
            
        // }else {
        //     Log::info('Request Payload', $request->all());
        //     return $this->sendError("Error Loading Data, Something went wrong",  $request->all(),  Response::HTTP_INTERNAL_SERVER_ERROR);
        // }
    }




    public function getCustomers(){

         $user = Auth::user();
         //return $user->authority->value;
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();

        $query = CRMDCustomers::query()->latest();


        $query->when($user->isRegion(), function ($query) use ($user) {
            return $query->where('region', $user->region);
        });

        $query->when($user->isBhub(), function ($query) use ($user) {
            return $query->where('region', $user->region)->where('hub', $user->bhub);
        });

        $query->when($user->isSCenter(), function ($query) use ($user) {
            return $query->where('region', $user->region)->where('hub', $user->bhub)->where('service_center', $user->service_center);
        });

        $query->when($user->isHQ(), function ($query) {
            // For HQ user, do not apply any additional conditions, return all results
            return $query;
        });

        // Default condition when none of the above conditions match
        if (!$user->isRegion() && !$user->isBhub() && !$user->isSCenter() && !$user->isHQ()) {
            $query->where('userid', $user->id);
        }

         $customers = $query->get();

        return $this->sendSuccess(CustomerCRMDResource::collection($customers), "CRMD Information Successfully Loaded", Response::HTTP_OK);
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
                $userRoleName = $getUserRoleObject['role'];
            } elseif ($request->approval_type == "Approved By BHM") {
                $updateData = ['approval_type' => 3, 'confirmed_by' => Auth::user()->id];
                $userRoleName = $getUserRoleObject['role'];
            } elseif ($request->approval_type == "Approved By Audit") {
                $updateData = ['approval_type' => 4, 'confirmed_by' => Auth::user()->id];
                $userRoleName = $getUserRoleObject['role'];
                //Run a script to update the customer table in the live Environment.
            }
        
            if (!empty($updateData)) {
                $customers->update($updateData);

                $icustomers = CRMDCustomers::where("id", $request->id)->first();

                // if($getUserRoleObject['role'] == 'teamlead'){
                //     $email =  User::where("bhub", $icustomers->hub)->value("email");
                // }

                $generateData = [
                    'email' => User::where("bhub", $icustomers->hub)->value("email"),
                    'id' =>  $request->id,
                    'accountNo' => $icustomers->AccountNo,
                    'meterNo' => $icustomers->MeterNo,
                    'name' => $icustomers->New_FullName,
                ];

                dispatch(new CRMDJOB($generateData))->delay(5);

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

            $customers = CRMDCustomers::where('id', $request->id)->update([
                'approval_type' => 5,
                'confirmed_by' => Auth::user()->id,
            ]);

        // $customers = CRMDCustomers::query()
        //     ->where('id', $request->id) // Find the record by ID
        //     ->when(
        //         !$request->approval_type || (!in_array($request->approval_type, [3, 4]) && $request->hub == $getUserRoleObject['business_hub'] && $getUserRoleObject['role'] == 'teamlead'),
        //         function ($query) use ($request) {
        //             return $query->update([
        //                 'approval_type' => $request->approval_type,
        //                 'confirmed_by' => Auth::user()->id,
        //             ]);
        //         }
        //     );

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


       
        public function getSingleCRMDdetails($id) {

        }


    public function getPendingCustomers(){

       

        try {

            $user = Auth::user();
            $getUserRoleObject = (new GeneralService)->getUserLevelRole();

           // return $getUserRoleObject['business_hub'];

            $customers = CRMDCustomers::query()->latest()
            ->when($getUserRoleObject['role'] == 'teamlead', function ($query) use ($getUserRoleObject, $user) {
                return $query->where([ 'approval_type' => 0])->where('hub', $getUserRoleObject['business_hub']);
            })
            ->when($getUserRoleObject['role'] == 'businesshub_manager', function ($query) use ($getUserRoleObject, $user) {
                return $query->where(['approval_type' => 1])->where('hub', $getUserRoleObject['business_hub']);
            })
            ->when($getUserRoleObject['role'] == 'audit', function ($query) use ($getUserRoleObject, $user) {
                return $query->where(['approval_type' => 2 ])->where('hub', $getUserRoleObject['business_hub']);
            })
            ->when($getUserRoleObject['role'] == 'billing', function ($query) use ($getUserRoleObject, $user) {
                return $query->where(['approval_type' => 3 ])->where('hub', $getUserRoleObject['business_hub']);
            })
            ->when($getUserRoleObject['role'] == 'hqbilling', function ($query) use ($getUserRoleObject, $user) {
                return $query->where(['approval_type' => 3 ]);
            })
            ->when($getUserRoleObject['role'] == 'admin', function ($query) use ($getUserRoleObject, $user) {
                return $query;
            })->get();

            return $this->sendSuccess(CustomerCRMDResource::collection($customers), "Record Successsfully Loaded", Response::HTTP_OK);

        }catch(\Exception $e){
            return $this->sendError($e->getMessage(), "No Result Found ". $getUserRoleObject['role'], Response::HTTP_BAD_REQUEST);
        }


    }



    public function getAllCustomers(){

        $user = Auth::user();
        //return $user->authority->value;
       $getUserRoleObject = (new GeneralService)->getUserLevelRole();

       $currentMonth = Carbon::now()->month;
       $currentYear = Carbon::now()->year;

       $query = CRMDCustomers::query()
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->latest();


       $query->when($user->isRegion(), function ($query) use ($user) {
           return $query->where('region', $user->region);
       });

       $query->when($user->isBhub(), function ($query) use ($user) {
           return $query->where('region', $user->region)->where('hub', $user->bhub);
       });

       $query->when($user->isSCenter(), function ($query) use ($user) {
           return $query->where('region', $user->region)->where('hub', $user->bhub)->where('service_center', $user->service_center);
       });

       $query->when($user->isHQ(), function ($query) {
           // For HQ user, do not apply any additional conditions, return all results
           return $query;
       });

       // Default condition when none of the above conditions match
       if (!$user->isRegion() && !$user->isBhub() && !$user->isSCenter() && !$user->isHQ()) {
           $query->where('userid', $user->id);
       }

        $customers = $query->paginate(100);

       return $this->sendSuccess(CustomerCRMDResource::collection($customers), "CRMD Information Successfully Loaded", Response::HTTP_OK);
   }


}


// $customers = CRMDCustomers::query()->latest()
// ->when($getUserRoleObject['role'] == 'teamlead', function ($query) use ($getUserRoleObject, $user) {
//     return $query->where('approval_type', 0)->where('hub', $getUserRoleObject['business_hub']);
// })
// ->when($getUserRoleObject['role'] == 'businesshub_manager', function ($query) use ($getUserRoleObject, $user) {
//     return $query->where('approval_type', 1)->where('hub', $getUserRoleObject['business_hub']);
// })
// ->when($getUserRoleObject['role'] == 'audit', function ($query) use ($getUserRoleObject, $user) {
//     return $query->where('approval_type', 2)->where('hub', $getUserRoleObject['business_hub']);
// })
// ->when($getUserRoleObject['role'] == 'billing', function ($query) use ($getUserRoleObject, $user) {
//     return $query->where('approval_type', 3)->where('hub', $getUserRoleObject['business_hub']);
// })
// ->when($getUserRoleObject['role'] == 'admin', function ($query) use ($user) {
//     return $query->where('userid', '=', $user->id);
// })
// ->get();