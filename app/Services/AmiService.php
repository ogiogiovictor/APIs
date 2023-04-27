<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;

class AmiService
{
    public function __construct()
    {
        //
    }

    public function getConnection($date){
        $getConnection = DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")
        // ->join("PowerSys.dbo.DATA_F_LOAD_PROFILE AS LP", "LP.MSNO", "FDAY.MSNO")
         ->join("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
         ->join("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")
         ->join("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
         ->join("PowerSys.dbo.ACHV_POWERGRID AS PG", "PG.ID", "PNG.ID")
         ->join("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
         ->join("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
         ->select("FDAY.MSNO", "FDAY.DATE", "FDAY.SAVEDB_TIME", "FDAY.BEGINTIME", "FDAY.ENDTIME", "FDAY.KWH_ABS", "FDAY.KWH_ABS_START", "FDAY.KWH_ABS_END", "PNG.Region", "PNG.BusinessHub", "PNG.Transformer", "SYS.Value AS AssetType")
         ->where("FDAY.BEGINTIME", '=',  $date)->where("SYS.Tag", '=', "CustomerType")->where("SYS.Value", '=', "Governments/Organizations")->paginate(1000);
     
         return $getConnection;
    }


    public function getSummary($request){
        $getConnection = DB::connection("ami")->table("PowerSys.dbo.ACHV_METER AS AC")
        ->leftJoin("PowerSys.dbo.ACHV_POC AS POC", "AC.ID", "POC.Meter_ID")
        ->leftJoin("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
        ->leftJoin("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY", "FDAY.MSNO", "AC.MSNO")
        ->leftJoin("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
        ->select("AC.MSNO", DB::raw('CONVERT(Varchar(50), SUM(Cast(FDAY.KWH_ABS as money)),1) as consumption'))
        ->where("SYS.Tag", '=', 'CustomerType')->where("SYS.Value", 'Governments/Organizations')
        ->whereYear("FDAY.BEGINTIME", $request->year)->whereMonth("FDAY.BEGINTIME", $request->month)
        ->groupBy('AC.MSNO')->get();

        return $getConnection;
    }

    public function getAmiReading($meterNo){
        $getConnection = DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")
        // ->join("PowerSys.dbo.DATA_F_LOAD_PROFILE AS LP", "LP.MSNO", "FDAY.MSNO")
         ->join("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
         ->join("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")
         ->join("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
         ->join("PowerSys.dbo.ACHV_POWERGRID AS PG", "PG.ID", "PNG.ID")
         ->join("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
         ->join("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
         ->select("FDAY.MSNO", "FDAY.DATE", "FDAY.SAVEDB_TIME", "FDAY.BEGINTIME", "FDAY.ENDTIME", "FDAY.KWH_ABS", "FDAY.KWH_ABS_START", "FDAY.KWH_ABS_END", "PNG.Region", "PNG.BusinessHub", "PNG.Transformer", "SYS.Value AS AssetType")
         ->where("FDAY.MSNO", '=',  $meterNo)->paginate(1000);
     
         return $getConnection;
    }

}
