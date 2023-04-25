<?php

namespace App\Http\Controllers\ACE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TransmissionStation;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;

class InjectionSubStationController extends BaseApiController
{
    public function getTransmissionStations(){

        try{
    
            $transmissionStations = TransmissionStation::all();

            return $this->sendSuccess($transmissionStations, "Transmission Stations Loaded", Response::HTTP_OK);
            
        }catch(\Exception $e) {
            return $this->sendError("No Transmission Stations", $e , Response::HTTP_UNAUTHORIZED);
        }
    }
}
