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

class CaadController extends BaseApiController
{
    public function getApproval(){

        $getAll = Caad::all();

        return $this->sendSuccess($getAll, "Get All Results", Response::HTTP_CREATED);

    }


    public function addCAAD(CaadRequest $request){

        $validator = Validator::make($request->all(), [
            'file_upload' => 'required|array',
            'file_upload.*' => 'file|mimes:jpeg,jpg,png,pdf|max:2048', // Add allowed file types here
            // other validation rules for other form fields if required
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation Error", $validator->errors(), Response::HTTP_BAD_REQUEST);
        }
    

        try {

            $processCAAD = ProcessCAAD::create([
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
                'amount' => $request->amount,
                'remarks' => $request->remarks,
                'file_upload_id' => 0,

            ]);

           
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
                        'process_caad_id' => $processCAAD->id,
                        'file_name' => $fileName,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getClientMimeType(),
                        'file_link' => 'customercaad/',
                    ]);
                }
    
            }

         

          return $this->sendSuccess($processCAAD, "File Successfully Uploaded", Response::HTTP_CREATED);

        }catch(\Exception $e){

            return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        

    }
}
