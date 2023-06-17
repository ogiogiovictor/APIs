<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Customer\CustomerAuthModel;

class CustomerAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $Authorization = $request->header('Authorization');

        if(empty($Authorization)){
            
            return response()->json([
                'status' => 403, 'message' => 'Required parameters to process your request is missing' . $request->header('Authorization'),
            ], Response::HTTP_BAD_REQUEST);
        }

        try{

            $matches = ['Authorization' => $Authorization];
            $checkRequest =  CustomerAuthModel::where($matches)->value("id");

            if($checkRequest){
                return $next($request); 
            }else{
                return response()->json([
                 'status' => 402, 'message' => 'Invalid Authorization Provided',
             ],  Response::HTTP_UNAUTHORIZED);
         
            }


        }catch(ModelNotFoundException $e){
            return response()->json(['message' => 'bad request, seems your token expired'], Response::HTTP_INTERNAL_SERVER_ERROR);

        }

        return $next($request);
    }
}
