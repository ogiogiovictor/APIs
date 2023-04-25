<?php

namespace App\Http\Controllers\ACE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\FeederEnum;
use App\Models\FeederEleven;
use App\Models\FeederThirty;
use App\Services\AssetService;


class FeederController extends BaseApiController
{

    public function index(Request $request) {


        $elevenA = FeederEleven::where("assettype", FeederEnum::FT_eleven()->value)->count();
        $thirtyA = Feederthirty::where("assettype", FeederEnum::FT_thirty_three()->value)->count();
        $total = FeederEleven::count() + Feederthirty::count();

        if($request->type == FeederEnum::FT_eleven()->value){  //11KV Feeder  11KV Feeder
            
            $feeder = (new AssetService)->getFeederEleven(); 
            

            $data = [
                'feeder_eleven' => $elevenA,
                'feeder_thirty' => $thirtyA,
                'total_feeder' => $total,
                'feeders' => $feeder,
             ];

             return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

         }else if($request->type == FeederEnum::FT_thirty_three()->value){
        
            $feeder = (new AssetService)->getFeederThirty(); 

            $data = [
                'feeder_eleven' => $elevenA,
                'feeder_thirty' => $thirtyA,
                'total_feeder' => $total,
                'feeders' => $feeder,
             ];

             return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);
        
        }else {

            $feeder = (new AssetService)->allFeeder();

            $data = [
                'feeder_eleven' => $elevenA,
                'feeder_thirty' => $thirtyA,
                'total_feeder' => $total,
                'feeders' => $feeder,
             ];
             return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);
        }
        
    }
}

