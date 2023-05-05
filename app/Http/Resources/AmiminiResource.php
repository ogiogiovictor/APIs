<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;


class AmiminiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "MSNO" => $this->MSNO,
            "consumption" => $this->consumption,
           /* "region" => DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")->join("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
            ->join("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")->join("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
            ->select("PNG.Region")
            ->where("FDAY.MSNO", '=',  $this->MSNO)->first()->Region,
            */
            "region" => $this->getName("Region"),
            "business_hub" => $this->getName("BusinessHub"),
          
             "BEGINTIME" => DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")->join("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
             ->join("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")->join("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
             ->select("FDAY.BEGINTIME")
             ->where("FDAY.MSNO", '=',  $this->MSNO)->first()->BEGINTIME,
            // "ENDTIME" => $this->ENDTIME,
             "Transformer" => $this->getName("Transformer"),

             "AssetType" => DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")->join("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
             ->join("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")->join("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
             ->join("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")->join("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
             ->select("SYS.Value")->where("SYS.Tag", '=', 'CustomerType')
             ->where("FDAY.MSNO", '=',  $this->MSNO)->first()->Value
        ];
    }

    public function getName($name) {
        return  DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")->join("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
        ->join("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")->join("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
        ->select("PNG.$name")
        ->where("FDAY.MSNO", '=',  $this->MSNO)->first()->$name;
    }
}
