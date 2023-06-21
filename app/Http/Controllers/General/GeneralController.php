<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DTWarehouse;
use App\Models\ServiceUnit;
use App\Models\FeederEleven;
use App\Models\FeederThirty;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;

class GeneralController extends BaseApiController
{
    public function getAllDrops(){

        $getDSS = DTWarehouse::select("Assetid", "DSS_11KV_415V_Name", "AssetType")->where("Status", "Active")->get()->toArray();
        $serviceUnit = ServiceUnit::select("Name", "Biz_Hub", "Region")->get();
        $serviceBand = ["E4H", "D8H", "C12H", "B16H", "A20H", "A18H"];
        $feeders11 = FeederEleven::select("Assetid", "F11kvFeeder_Name")->get()->toArray();
        $feeders33 = FeederThirty::select("Assetid", "F33kv_Feeder_Name")->get()->toArray();

        $data = [
            'dss' => $getDSS,
            'service_unit' => $serviceUnit,
            'service_band' => $serviceBand,
            'feeder11' => $feeders11,
            'feeder33' => $feeders33,
        ];


        return $this->sendSuccess($data, "loaded Successfully", Response::HTTP_OK);

    }
}
