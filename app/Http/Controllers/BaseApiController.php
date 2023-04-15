<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use Symfony\Component\HttpFoundation\Response;

class BaseApiController extends Controller
{
    public function sendSuccess($data, $message="", $response)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            //'status' => $response
        ], Response::HTTP_OK);
    }


    public function sendError($error, $errorMessages = [])
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }

        return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
    }


   public static function logIssuess($request){
            
        //"-".Request::route()->getName()
        //request->route()->getActionMethod()
         $logs = AuditLog::create([
             'user_id' => $request->user_id,
             'route' => $request->route,
             'message' => $request->message,
             'action' => $request->action,
             'ip_address' => $request->ip_address,
             'browser' => $request->browser,
             'device' => $request->device,
        ]);

        return self::respond($logs);
        // return $logs;
       
    }
}
