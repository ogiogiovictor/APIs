<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

class AfterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);

        // // Handle the request and get the response
        // $response = $next($request);

        // // Save the request and response with user information
        // $log = new Log();
        // $log->user_id = Auth::id(); // Assuming you have user authentication
        // $log->ip_address = $request->ip();
        // $log->geocode = $this->getGeocode($request);
        // $log->request = $request->all();
        // $log->response = $response->getContent();
        // $log->save();

        // return $response;

    }

    private function getGeocode(Request $request)
    {
        $ipAddress = $request->ip();
    
        $client = new Client();
        $response = $client->get("https://geocode.xyz/{$ipAddress}?json=1");
        $data = json_decode($response->getBody(), true);

        // Extract the latitude and longitude from the response
        $latitude = $data['latt'];
        $longitude = $data['longt'];

        return compact('latitude', 'longitude');
    }
}
