<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApplicationAccess;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Oauthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $get_allowed_ip = ApplicationAccess::pluck("ip_address")->toArray();
     
        $myIP = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : request()->ip(); 

        if(!in_array($myIP, $get_allowed_ip)){
            return response()->json([
                'status' => 401, 'message' => 'Host IP Not Allowed YES' . $_SERVER['HTTP_HOST']. " -request- ".request()->ip(). " serverip- ". $_SERVER['REMOTE_ADDR'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $appSecret = $request->header('app-secret');
        $appToken = $request->header('app-token');

        if(empty($appSecret) || empty($appToken)){
            
            return response()->json([
                'status' => 403, 'message' => 'Required parameters to process your request is missing' . $request->header('app-secret'),
            ], Response::HTTP_BAD_REQUEST);
        }

        try{

            $matches = ['app-secret' => $appSecret, 'app-token' => $appToken, 'status' => 'on'];
            $checkRequest =  ApplicationAccess::where($matches)->value("id");

            if($checkRequest){
                return $next($request); 
            }else{
                return response()->json([
                 'status' => 402, 'message' => 'Invalid Header Information',
             ],  Response::HTTP_UNAUTHORIZED);
         
            }

        }catch (ModelNotFoundException $e) { 
            return response()->json(['message' => 'bad request, seems your token expired'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return $next($request);
    }
}
