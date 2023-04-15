<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IPListing;
use Illuminate\Support\Facades\URL;

class ManageService
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $checkIfExist = IPListing::where("ip_address", $_SERVER['HTTP_HOST'])->value("ip_address");
       

        if(!$checkIfExist){
            IPListing::create([
                'domain_name' =>  $_SERVER["SERVER_NAME"], // Agent::platform(),
                'ip_address'  => $_SERVER['HTTP_HOST'], //$request->ip(),
                'route' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : URL::current(),  //, //Route::currentRouteName(),
            ]);
        }

        return $next($request);
    }
}
