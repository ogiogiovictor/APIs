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
use App\Http\Requests\FeederRequest;
use App\Helpers\AssetHelper;


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




    public function addFeeder(FeederRequest $request){ 

        $request['AssetName'] = $request['F11kvFeeder_Name'];
        $assetData = AssetHelper::dataRequest($request);

        if($request->assettype == "11KV Feeder"){

            $createFeeder = FeederEleven::create($assetData);

        }else {

            $createFeeder = FeederThirty::create([
                'F33kv_Feeder_Name' => $assetData['F33kv_Feeder_Name'],
                'AssetName' => $assetData['F33kv_Feeder_Name'],
                'assettype' => $assetData['assettype'],
                'latitude' => $assetData['latitude'],
                'longtitude' => $assetData['longtitude'],
                'naccode' => $assetData['naccode'],
                'F33kv_Regional_Name' => $assetData['F33kv_Regional_Name'],
                'F33kv_Business_Hub_Name' => $assetData['F33kv_Business_Hub_Name'],
                'F33kV_Feeder_Circuit_Breaker_Make' => $assetData['F33kV_Feeder_Circuit_Breaker_Make'],
                'F33kV_Feeder_Circuit_Breaker_Type' => $assetData['F33kV_Feeder_Circuit_Breaker_Type'],
                'F33kV_Upriser_Cable_Type' => $assetData['F33kV_Upriser_Cable_Type'],
                'F33kv_Teeoffs' => $assetData['F33kv_Teeoffs'],
                'F33kv_Tee_offs_Coordinate' => $assetData['F33kv_Tee_offs_Coordinate'],
                'F33kv_Substations_capacity' => $assetData['F33kv_Substations_capacity'],
                'F33kv_lineload_coordinate' => $assetData['F33kv_lineload_coordinate'],
                'F33kv_Conductor_Size' => $assetData['F33kv_Conductor_Size'],
                'F33kv_Aluminium_Conductor' => $assetData['F33kv_Aluminium_Conductor'],
                'F33kv_Commisioning' => $assetData['F33kv_Commisioning'],
                'Capture DateTime' => date('Y-m-d H:i:s'),
                'Synced DateTime' => date('Y-m-d H:i:s'),
            ]);

        }
        return $this->sendSuccess($createFeeder, "Asset Information Saved Successfully", Response::HTTP_OK);

    }



}

