<?php

namespace App\Http\Controllers\CAAD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Caad;
use App\Http\Requests\CaadRequest;
use App\Models\FileCAAD;
use App\Models\ProcessCAAD;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Services\GeneralService;
use App\Enums\CaadEnum;
use App\Models\CAADCommentApproval;
use App\Imports\CAADImport;
use App\Models\BulkCAAD;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class CaadController extends BaseApiController
{
    public function getApproval(){

        $getAll = Caad::all();

        return $this->sendSuccess($getAll, "Get All Results", Response::HTTP_CREATED);

    }


    public function addCAAD(CaadRequest $request){

        try {


            if(isset($request->update_id)){
                $getwhocreated = ProcessCAAD::find($request->update_id);
                // if($getwhocreated->created_by  != $request->update_id){
                //     return $this->sendError("Error", "You are not authorized to update this request", Response::HTTP_BAD_REQUEST);
                // }
                
                //update CAAD information 
                $processCAAD = ProcessCAAD::where("id", $request->update_id)->update([
                    'accountNo' => $request->accountNo,
                    'phoneNo' => $request->phoneNo,
                    'surname' => $request->surname,
                    'lastname' => $request->lastname,
                    'othername' => $request->othername,
                    'service_center' => $request->service_center,
                    'meterno' => $request->meterno,
                    'accountType' => $request->accountType,
                    'transtype' => $request->transtype,
                    'meter_reading' => $request->meter_reading,
                    'transaction_type' => $request->transaction_type,
                    'effective_date' => $request->effective_date,
                    'amount' => isset($request->amount) ? $request->amount : $getwhocreated->amount,
                    'remarks' => $request->remarks,
                    'file_upload_id' => 0,
                    'business_hub' => $request->business_hub,
                    'region' => $request->region,
                    'created_by' => Auth::user()->id
                  //  'status' => 0
                ]);

            }else {

                $validator = Validator::make($request->file('file_upload'), [
                    'file_upload' => 'sometimes|nullable|array', // Ensure it's an array of files
                    'file_upload.*' => 'sometimes|nullable|mimes:jpeg,jpg,png,pdf,csv,xlsx|max:2048', // Add allowed file types here
                    // other validation rules for other form fields if required
                ]);
        
                $validator->after(function ($validator) use ($request) {
                    $files = $request->file('file_upload');
            
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            if ($file !== null && !$file->isValid()) {
                                $validator->errors()->add('file_upload', 'Invalid file upload.');
                                break; // Stop processing if any file is invalid
                            }
                        }
                    }
                });
            
                if ($validator->fails()) {
                    return $this->sendError("Validation Error", $validator->errors(), Response::HTTP_BAD_REQUEST);
                }

                $processCAAD = ProcessCAAD::create([
                    'accountNo' => $request->accountNo,
                    'phoneNo' => $request->phoneNo,
                    'surname' => $request->surname,
                    'lastname' => $request->lastname,
                    'othername' => $request->othername,
                    'service_center' => isset($request->service_center) ? $request->service_center : 0,
                    'meterno' => isset($request->meterno) ?: $request->meterno,
                    'accountType' => $request->accountType,
                    'transtype' => $request->transtype,
                    'meter_reading' => isset($request->meter_reading) ?: $request->meter_reading,
                    'transaction_type' => $request->transaction_type,
                    'effective_date' => $request->effective_date,
                    'amount' => $request->amount,
                    'remarks' => $request->remarks,
                    'file_upload_id' => 0,
                    'business_hub' => $request->business_hub,
                    'region' => $request->region,
                    'created_by' => Auth::user()->id,
    
                ]);

            } 

           
              // Check if the destination folder exists and has write permissions is_writable
              $destinationPath = public_path('customercaad/');
              if (!file_exists($destinationPath)) {
                  return $this->sendError("Error", "Destination folder 'customercaad' is does not exist.", Response::HTTP_INTERNAL_SERVER_ERROR);
              }else if(!is_writable($destinationPath)){
                  return $this->sendError("Error", "Destination folder 'customercaad' is not writable.", Response::HTTP_INTERNAL_SERVER_ERROR);
              }

             
             //Handle file upload
            if ($request->has('file_upload')) {

                $files = $request->file('file_upload');
               
                foreach ($files as $file) {

                    $timestamp = now()->timestamp; // Generate the current Unix timestamp
                    $date = now()->format('Ymd'); // Format the current date as YYYYMMDD  
                    // Combine the timestamp, date, and extension to form the unique filename
                    $uniqueFileName = $date . '_' . $timestamp;

                    $fileName = $uniqueFileName.''. uniqid() . '_' . $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();

                    $file->storeAs('customercaad', $fileName, 'public');

                    $uploadfile = FileCAAD::create([
                        'process_caad_id' => isset($request->update_id) ? $request->update_id : $processCAAD->id,
                        'file_name' => $fileName,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getClientMimeType(),  // $file->getMimeType(),
                        'file_link' => 'customercaad/',
                    ]);
                }
    
            }

            $getUpdate = isset($request->update_id) ? $getwhocreated->where("id", $request->update_id)->first() : $processCAAD;
         

          return $this->sendSuccess($getUpdate, "File Successfully Uploaded", Response::HTTP_CREATED);

        }catch(\Exception $e){

            return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        

    }





    public function getAllCAAD(){
        // Assuming you have already retrieved the user role
        $userRole = Auth::user()->roles->pluck('name')->first();
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();

        $getSingleCAAD = ProcessCAAD::with('fileUpload')->with('CaadComment')
           // ->where('batch_type', 'single')
            ->when($userRole === 'district_accountant', function ($query) use ($getUserRoleObject) {
                return $query->where('status', CaadEnum::PENDING->value)->where("business_hub", $getUserRoleObject['business_hub']);
            })
            ->when($userRole === 'businesshub_manager', function ($query) use ($getUserRoleObject) {
                return $query->where('status', CaadEnum::APPROVED_BY_DISTRICT_ACCOUNTANT->value)->where("business_hub", $getUserRoleObject['business_hub']);
            })
            ->when($userRole === 'audit', function ($query) use ($getUserRoleObject) {
                return $query->where('status', CaadEnum::APPROVED_BY_BUSINESS_HUB_MANAGER->value)->where("region", $getUserRoleObject['region']);
            })
            ->when($userRole === 'regional_manager', function ($query) use ($getUserRoleObject) {
                return $query->where('status', CaadEnum::APPROVED_BY_AUDIT->value)->where("region", $getUserRoleObject['region']);
            })
            ->when($userRole === 'hcs', function ($query) {
                return $query->where('status', CaadEnum::APPROVED_BY_REGIONAL_MANAGER->value);
            })
            ->when($userRole === 'cco', function ($query) {
                return $query->where('status', CaadEnum::APPROVED_BY_HCS->value);
            })
            ->when($userRole === 'md', function ($query) {
                return $query->where('status',  CaadEnum::APPROVED_BY_CCO->value);
            }) 
            ->when($userRole === 'admin', function ($query) {
                return $query->whereIn('status', [0, 1, 2, 3, 4, 5, 6, 7, 10]);
            })->when($userRole === 'billing', function ($query) {
                return $query->whereIn('status', CaadEnum::APPROVED_BY_MD->value);
            })
            ->orderBy('id', 'desc')
        ->paginate(20);


        $getBatchCAAD = BulkCAAD::with('withmanycaads')->withCount('withmanycaads')->with('withmayncomments')->withCount('withmayncomments')
            ->when($userRole === 'district_accountant', function ($query) use ($getUserRoleObject) {
                return $query->where('batch_status', CaadEnum::PENDING->value)->where("business_hub", $getUserRoleObject['business_hub']);
            })
            ->when($userRole === 'businesshub_manager', function ($query) use ($getUserRoleObject) {
                return $query->where('batch_status', CaadEnum::APPROVED_BY_DISTRICT_ACCOUNTANT->value)->where("business_hub", $getUserRoleObject['business_hub']);
            })
            ->when($userRole === 'audit', function ($query) use ($getUserRoleObject) {
                return $query->where('batch_status', CaadEnum::APPROVED_BY_BUSINESS_HUB_MANAGER->value)->where("region", $getUserRoleObject['region']);
            })
            ->when($userRole === 'regional_manager', function ($query) use ($getUserRoleObject) {
                return $query->where('batch_status', CaadEnum::APPROVED_BY_AUDIT->value)->where("region", $getUserRoleObject['region']);
            })
            ->when($userRole === 'hcs', function ($query) {
                return $query->where('batch_status', CaadEnum::APPROVED_BY_REGIONAL_MANAGER->value);
            })
            ->when($userRole === 'cco', function ($query) {
                return $query->where('batch_status', CaadEnum::APPROVED_BY_HCS->value);
            })
            ->when($userRole === 'md', function ($query) {
                return $query->where('batch_status',  CaadEnum::APPROVED_BY_CCO->value);
            }) 
            ->when($userRole === 'admin', function ($query) {
                return $query->whereIn('batch_status', [0, 1, 2, 3, 4, 5, 6, 7, 10]);
            })->when($userRole === 'billing', function ($query) {
                return $query->whereIn('status', CaadEnum::APPROVED_BY_MD->value);
            })
        ->orderBy('id', 'desc')->paginate(20);

        $data = [
            'single' => $getSingleCAAD,
            'batch' => $getBatchCAAD
        ];

        return $this->sendSuccess($data, "Record Successfully Updated", Response::HTTP_OK);

    }



    public function BulkCAADUpload(Request $request){
       
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);
       
          //Handle file upload
         if ($request->has('file')) {

          $file = $request->file('file');

                $timestamp = now()->timestamp; // Generate the current Unix timestamp
                $date = now()->format('Ymd'); // Format the current date as YYYYMMDD  
                // Combine the timestamp, date, and extension to form the unique filename
                $uniqueFileName = $date . '_' . $timestamp;
 
                $fileName = $uniqueFileName.''. uniqid() . '_' . $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();

                $file->storeAs('customercaad', $fileName, 'public');

                $bulkCAAD = BulkCAAD::create([
                    'batch_name' => $request->batch_name,
                    'business_hub' => $request->business_hub,
                    'bulk_unique_id' => uniqid() . '_'. $date.''.$timestamp,
                    'batch_file_name' => $fileName,
                    'region' => $request->region
                ]);
        
                $batch_id = $bulkCAAD->id; // Get the batch_id from the newly created BulkCAAD model

                
        }

      
        $result =  Excel::import(new CAADImport($bulkCAAD),  $file);

        return $this->sendSuccess(200, "Record Successfully Updated", Response::HTTP_OK);
         
        
    }


    private function moveApprovalStatus(string $userRole, int $amount)
    {
        $getLimit = Caad::where("role", $userRole)->value("end_limit");
        // Define the role-based approval mapping
        $roleApprovalMapping = [
            'district_accountant' => CaadEnum::APPROVED_BY_DISTRICT_ACCOUNTANT->value,
            'businesshub_manager' => CaadEnum::APPROVED_BY_BUSINESS_HUB_MANAGER->value,
            'audit' => CaadEnum::APPROVED_BY_AUDIT->value,
            'regional_manager' => ($amount <= $getLimit)  ?  CaadEnum::APPROVED_BY_MD->value :  CaadEnum::APPROVED_BY_REGIONAL_MANAGER->value,
            'hcs' => ($amount <= $getLimit) ? CaadEnum::APPROVED_BY_MD->value  : CaadEnum::APPROVED_BY_HCS->value,
            'cco' => ($amount <= $getLimit) ? CaadEnum::APPROVED_BY_MD->value : CaadEnum::APPROVED_BY_CCO->value,
            'md' => CaadEnum::APPROVED_BY_MD->value,
            'billing' => CaadEnum::BILLING->value,
            'admin' => CaadEnum::ADMIN->value,
        ];

        // Check if the user role exists in the mapping, otherwise, throw an exception
        if (!array_key_exists($userRole, $roleApprovalMapping)) {
            throw new \Exception('Invalid user role');
        }

        return $roleApprovalMapping[$userRole];
    }



        private function getApprovalStatus(string $userRole, int $amount)
        {
            $getLimit = Caad::where("role", $userRole)->value("end_limit");
            // Define the role-based approval mapping
            $roleApprovalMapping = [
                'district_accountant' => CaadEnum::APPROVED_BY_DISTRICT_ACCOUNTANT->value,
                'businesshub_manager' => CaadEnum::APPROVED_BY_BUSINESS_HUB_MANAGER->value,
                'audit' => $this->checkWheretopush($amount, $userRole),
                'regional_manager' => ($amount <= $getLimit) ? CaadEnum::APPROVED_BY_REGIONAL_MANAGER->value : $this->checkWheretopush($amount, $userRole),
                'hcs' => ($amount <= $getLimit) ? CaadEnum::APPROVED_BY_HCS->value : $this->checkWheretopush($amount, $userRole),
                'cco' => ($amount <= $getLimit) ? CaadEnum::APPROVED_BY_CCO->value : $this->checkWheretopush($amount, $userRole),
                'md' => CaadEnum::APPROVED_BY_MD->value,
                'billing' => CaadEnum::BILLING->value,
                'admin' => CaadEnum::ADMIN->value,
            ];

            // Check if the user role exists in the mapping, otherwise, throw an exception
            if (!array_key_exists($userRole, $roleApprovalMapping)) {
                throw new \Exception('Invalid user role');
            }

            return $roleApprovalMapping[$userRole];
        }

        private function checkWheretopush($amount, $role) {
            // Get the role's approval limit from the database
            $limit = Caad::where("approvals", 1)->where("role", $role)->first();
        
            if (!$limit) {
                // If the role's limit is not found, it cannot approve, so go to the next role.
                return $this->getNextRoleApproval($amount, $role);
            }
        
            $end_limit = $limit->end_limit;
        
            if ((int)$amount <= (int)$end_limit) {
                // If the amount is within the limit, approve
                return $this->getApprovalStatusForRole($role);
            } else {
                // If the amount is beyond the limit, go to the next role.
                return $this->getNextRoleApproval($amount, $role);
            }
        }


        private function getNextRoleApproval($amount, $currentRole) {
            // Define the role approval order
            $roleApprovalOrder = [
                'audit', 'regional_manager', 'hcs', 'cco', 'md', 'billing'
            ];
        
            // Get the index of the current role in the order
            $currentIndex = array_search($currentRole, $roleApprovalOrder);
        
             // Find the next role to check for approval
            for ($i = $currentIndex + 1; $i < count($roleApprovalOrder); $i++) {
                $nextRole = $roleApprovalOrder[$i];
                // Check if the next role has an approval limit in the database
                $nextLimit = Caad::where("approvals", 1)->where("role", $nextRole)->first();
                if ($nextLimit) {
                    if ((int)$amount <= (int)$nextLimit->end_limit) {
                        // If the next role has a limit and the amount is within it, approve
                        return $this->getApprovalStatusForRole($nextRole);
                    }
                } else {
                    // If the next role doesn't have an approval limit, go to the next one.
                    continue;
                }
            }
        
            // If no roles are left, return a default status (e.g., rejected)
            return CaadEnum::APPROVED_BY_REGIONAL_MANAGER;
        }


        private function getApprovalStatusForRole($role) {
            // Define the approval statuses for each role
            $roleApprovalMapping = [
                'audit' => CaadEnum::APPROVED_BY_AUDIT->value,
                'regional_manager' => CaadEnum::APPROVED_BY_REGIONAL_MANAGER->value,
                'hcs' => CaadEnum::APPROVED_BY_HCS->value,
                'cco' => CaadEnum::APPROVED_BY_CCO->value,
                'md' => CaadEnum::APPROVED_BY_MD->value,
                'billing' => CaadEnum::BILLING->value,
               // 'admin' => CaadEnum::ADMIN->value,
            ];
        
            // Return the approval status for the given role
            return $roleApprovalMapping[$role];
        }


  


    public function CaadApprovalRequest(Request $request){

          try{

            $amount = (int)$request->amount;
              // Get the user role
              $userRole = Auth::user()->roles->pluck('name')->first();
              DB::beginTransaction();
              // Check if the batch type is single
                  if ($request->batch_type == 'single') {
                      // Update the process CAAD status
                      $processCAAD = ProcessCAAD::find($request->id);
                      $processCAAD->status = $this->moveApprovalStatus($userRole,  $amount);
                      $processCAAD->save();

                      $this->passPosition($userRole, $request->id, $request->batch_type);
                  }else {
                      
                      $processBatch = BulkCAAD::find($request->id);
                      $processBatch->batch_status = $this->moveApprovalStatus($userRole,  $amount);
                      $processBatch->save();

                      //Now Update the processCADD where batch id is = batch
                      $processCARD = ProcessCAAD::where('batch_id', $request->id)->update([
                          'status' => $this->moveApprovalStatus($userRole,  $amount)
                      ]);

                      $this->passPosition($userRole, $request->id, $request->batch_type);
                  }

                  // Add a comment
                 $secret =  $this->addComment($request, $userRole);
                  DB::commit();
                  return $this->sendSuccess($secret, "CAAD Successfully Approved", Response::HTTP_CREATED);

          } catch(\Exception $e){
              DB::rollBack();
              return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
          }
         

      }


  


      private function passPosition($userRole, $id, $batchType){
        $user = Auth::user()->id;
        $positions = [
            'district_accountant' => 'district_accountant',
            'busienesshub_manager' => 'business_hub_manager',
            'audit' => 'audit',
            'regional_manager' => 'regional_manager',
            'hcs' => 'hcs',
            'cco' => 'cco',
            'md' => 'md',
        ];
    
       // $table = $batchType === 'single' ? 'ProcessCAAD' : 'BulkCAAD';
          // Determine the table based on the batch type
        $table = $batchType === 'single' ? ProcessCAAD::class : BulkCAAD::class;

     
        
        

        if (array_key_exists($userRole, $positions)) {
            $column = $positions[$userRole];
    
            // Update the specified column with the user ID
            //$affectedRows = DB::table($table)->where('id', $id)->update([$column => $user]);
              // Update the specified column with the user ID
            $affectedRows = $table::where('id', $id)->update([$column => $user]);

    
            return $affectedRows; // Return the number of affected rows
        }
    
        return 0; // Return 0 if the user role is not found in the positions array
    }



   
    private function addComment(Request $request, string $userRole)
    {

        // Get the label for the given userRole from the CaadEnum
        $userRoleLabel = match ($userRole) {
            'district_accountant' => CaadEnum::APPROVED_BY_DISTRICT_ACCOUNTANT->label(),
            'businesshub_manager' => CaadEnum::APPROVED_BY_BUSINESS_HUB_MANAGER->label(),
            'audit' => CaadEnum::APPROVED_BY_AUDIT->label(),
            'regional_manager' => CaadEnum::APPROVED_BY_REGIONAL_MANAGER->label(),
            'hcs' => CaadEnum::APPROVED_BY_HCS->label(),
            'cco' => CaadEnum::APPROVED_BY_CCO->label(),
            'md' => CaadEnum::APPROVED_BY_MD->label(),
            'admin' => CaadEnum::ADMIN->label(),
            default => '',
        };

      return  $caadComment = CAADCommentApproval::create([
            'process_caad_id' => $request->id,
            'approval_type' => $request->batch_type,
            'batch_id' => isset($request->batch_id) ? $request->batch_id : 0,
            'approval_by' => Auth::user()->name,
            'comments' =>  $userRoleLabel . ' @ ' . ' ' . Date('Y-m-d H:i:s'),
        ]);
    }


    private function addRejectComment(Request $request, string $userRole)
    {

        $caadComment = CAADCommentApproval::create([
            'process_caad_id' => $request->id,
            'approval_type' => $request->batch_type,
            'batch_id' => isset($request->batch_id) ? $request->batch_id : 0,
            'approval_by' => Auth::user()->name,
            'comments' =>  'Rejected By ' . $userRole. ' @ ' . ' ' . Date('Y-m-d H:i:s'),
        ]);
    }


    


      public function CaadRejectRequest(Request $request){
  
        try{
            // Get the user role
            $userRole = Auth::user()->roles->pluck('name')->first();
            DB::beginTransaction();
            // Check if the batch type is single
                if ($request->batch_type == 'single') {
                    // Update the process CAAD status
                    $processCAAD = ProcessCAAD::find($request->id);
                    $processCAAD->status = 10;
                    $processCAAD->save();
                }else {

                  $processBatch = BulkCAAD::find($request->id);
                  $processBatch->batch_status = 10;
                  $processBatch->save();

                  //Now Update the processCADD where batch id is = batch
                  $processCARD = ProcessCAAD::where('batch_id', $request->id)->update([
                      'status' => 10  // Status 10 is for rejection
                  ]);

                }

                // Add a comment
                $this->addRejectComment($request, $userRole);
                DB::commit();
                return $this->sendSuccess($processCAAD, "CAAD Successfully Approved", Response::HTTP_CREATED);

        } catch(\Exception $e){
            DB::rollBack();
            return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
       

    }


    public function allCAAD(){
        // Assuming you have already retrieved the user role
        $userid = Auth::user()->id;
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();
        $userRole = Auth::user()->roles->pluck('name')->first();

        $getSingleCAAD = ProcessCAAD::with('fileUpload')->with('CaadComment')
                //->where('batch_type', 'single')
                ->when($userRole === 'credit_control', function ($query)  use ($userid, $getUserRoleObject){
                    return $query->where('created_by', $userid)->where("region", $getUserRoleObject['region']);
                })
                ->when($userRole === 'district_accountant', function ($query) use ($userid, $getUserRoleObject) {
                    return $query->where('district_accountant', $userid)->where("business_hub", $getUserRoleObject['business_hub']);
                })
                ->when($userRole === 'businesshub_manager', function ($query) use ($userid, $getUserRoleObject) {
                    return $query->where('business_hub_manager', $userid)->where("business_hub", $getUserRoleObject['business_hub']);
                })
                ->when($userRole === 'audit', function ($query) use ($userid, $getUserRoleObject) {
                    return $query->where('audit', $userid)->where("business_hub", $getUserRoleObject['business_hub']);
                })
                ->when($userRole === 'regional_manager', function ($query) use ($userid, $getUserRoleObject) {
                    return $query->where('regional_manager', $userid)->where("region", $getUserRoleObject['region']);
                })
                ->when($userRole === 'hcs', function ($query) {
                    return $query->whereIn('accountType', ['Prepaid', 'Postpaid']);
                })
                ->when($userRole === 'cco', function ($query) {
                    return $query->whereIn('accountType', ['Prepaid', 'Postpaid']);
                })
                ->when($userRole === 'md', function ($query) {
                    return $query->where('accountType',  ['Prepaid', 'Postpaid']);
                }) 
                ->when($userRole === 'admin', function ($query) {
                    return $query->whereIn('status', [0, 1, 2, 3, 4, 5, 6, 7, 10]);
                }, function ($query) {
                    // Default logic for roles that do not match any of the previous conditions
                    return $query->whereIn('accountType', ['Prepaid', 'Postpaid']);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(30);


            $getBatchCAAD = BulkCAAD::with('withmanycaads')->withCount('withmanycaads')
                ->with('withmayncomments')->withCount('withmayncomments')
                ->when(in_array($userRole, ['district_accountant', 'businesshub_manager', 'audit']), function ($query) use ($userRole, $userid, $getUserRoleObject) {
                    $query->where('business_hub', $getUserRoleObject['business_hub']);

                    if ($userRole === 'district_accountant') {
                        $query->where('district_accountant', $userid);
                    } elseif ($userRole === 'businesshub_manager') {
                        $query->where('business_hub_manager', $userid);
                    } elseif ($userRole === 'audit') {
                        $query->where('batch_status', $userid);
                    }

                    return $query;
                })
                ->when($userRole === 'regional_manager', function ($query) use ($userid, $getUserRoleObject) {
                    return $query->where('regional_manager', $userid)->where('region', $getUserRoleObject['region']);
                })
                ->when(in_array($userRole, ['hcs', 'cco', 'md', 'admin']), function ($query) {
                    return $query->orderBy('created_at', 'desc');
                })
                ->orderBy('id', 'desc')
            ->paginate(10);


        $data = [
            'single' => $getSingleCAAD,
            'batch' => $getBatchCAAD
        ];

        return $this->sendSuccess($data, "Record Successfully Updated", Response::HTTP_OK);

    }






}
