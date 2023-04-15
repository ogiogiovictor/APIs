<?php

namespace App\Http\Controllers\ACE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\FeederEnum;
use App\Models\FeederEleven;
use App\Services\AssetService;


class FeederController extends BaseApiController
{

    public function index(Request $request) {


        if($request->type == FeederEnum::FT_eleven()->value){  //11KV Feeder  11KV Feeder
            
            $feeder = (new AssetService)->getFeederEleven(); 
            return $feeder;

         }else if($request->type == FeederEnum::FT_thirty_three()->value){
        
            $feeder = (new AssetService)->getFeederThirty(); 
            return $feeder;
        
        }else {

            $feeders = (new AssetService)->allFeeder();
            return $feeders;
        }
        
    }
}

