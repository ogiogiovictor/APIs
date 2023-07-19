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

class CaadController extends BaseApiController
{
    public function getApproval(){

        $getAll = Caad::all();

        return $this->sendSuccess($getAll, "Get All Results", Response::HTTP_CREATED);

    }


    public function addCAAD(Request $request){

      // $file =  $request->file('file_upload');
        try {

           

             //Handle file upload
            if ($request->has('file_upload')) {
                $file = $request->file('file_upload');
                $fileName = $file->getClientOriginalName();

                 // Check if the destination folder exists and has write permissions is_writable
            $destinationPath = public_path('customercaad/');

            //return $destinationPath;

            if (!file_exists($destinationPath)) {
                return $this->sendError("Error", "Destination folder 'customercaad' is does not exist.", Response::HTTP_INTERNAL_SERVER_ERROR);
            }else if(!is_writable($destinationPath)){
                return $this->sendError("Error", "Destination folder 'customercaad' is not writable.", Response::HTTP_INTERNAL_SERVER_ERROR);
            }


            $file->storeAs('customercaad', $fileName, 'public');

               
            
            }else {
            return $this->sendError("Error", "File not found in the request.", Response::HTTP_BAD_REQUEST);
        }

         

          return $this->sendSuccess("Completed", "Get All Results", Response::HTTP_CREATED);

        }catch(\Exception $e){

            return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        

    }
}
