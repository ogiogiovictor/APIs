<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Models\DTWarehouse;
use Illuminate\Support\Carbon;
use App\Http\Resources\AmiminiResource;

class AmiService
{
    public function allConnections($request){

     
        // return $request;
        $reqStatus = '';

        if ($request === 'DT') {
            $reqStatus = 'DT';
        } elseif ($request === 'Feeder') {
            $reqStatus = 'Feeder';
        } elseif ($request === 'Non-MD') {
            $reqStatus = 'Non-MD';
        }elseif ($request === 'MD') {
            $reqStatus = 'MD';
        }elseif ($request === 'Governments/Organizations') {
            $reqStatus = 'Governments/Organizations';
        }

        $reqStatus = trim(stripslashes($request), '"');

        $getConnection = DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")
        // ->join("PowerSys.dbo.DATA_F_LOAD_PROFILE AS LP", "LP.MSNO", "FDAY.MSNO")
         ->leftJoin("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
         ->leftJoin("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")
         ->leftJoin("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
         ->leftJoin("PowerSys.dbo.ACHV_POWERGRID AS PG", "PG.ID", "PNG.ID")
         ->leftJoin("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
         ->leftJoin("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
         ->select("FDAY.MSNO", "FDAY.DATE", "FDAY.SAVEDB_TIME", "FDAY.BEGINTIME", "FDAY.ENDTIME", "FDAY.KWH_ABS", "FDAY.KWH_ABS_START", "FDAY.KWH_ABS_END", "PNG.Region", "PNG.BusinessHub", "PNG.Transformer", "SYS.Value AS AssetType")
         ->where("SYS.Tag", '=', "CustomerType")->where("SYS.Value", $reqStatus)->orderBy("FDAY.SAVEDB_TIME", "DESC")->paginate(100);
     
         return $getConnection;
    }


    public function allConnectionsgroups(){
       
         $getConnection = DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")
         ->join("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
         ->join("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")
         ->join("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
         ->join("PowerSys.dbo.ACHV_POWERGRID AS PG", "PG.ID", "PNG.ID")
         ->join("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
         ->join("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
         ->selectRaw("SYS.Value AS AssetType, COUNT(DISTINCT FDAY.MSNO) as total")
         //->whereNotNull("FDAY.KWH_ABS")
         ->where("SYS.Tag", "=", "CustomerType")
         ->groupBy("SYS.Value")
         ->get();
      
     return $getConnection;
    }


    public function getConnection($date){
        $getConnection = DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")
        // ->join("PowerSys.dbo.DATA_F_LOAD_PROFILE AS LP", "LP.MSNO", "FDAY.MSNO")
         ->leftJoin("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
         ->leftJoin("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")
         ->leftJoin("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
         ->leftJoin("PowerSys.dbo.ACHV_POWERGRID AS PG", "PG.ID", "PNG.ID")
         ->leftJoin("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
         ->leftJoin("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
         ->select("FDAY.MSNO", "FDAY.DATE", "FDAY.SAVEDB_TIME", "FDAY.BEGINTIME", "FDAY.ENDTIME", "FDAY.KWH_ABS", "FDAY.KWH_ABS_START", "FDAY.KWH_ABS_END", "PNG.Region", "PNG.BusinessHub", "PNG.Transformer", "SYS.Value AS AssetType")
         ->where("FDAY.BEGINTIME", '=',  $date)->where("SYS.Tag", '=', "CustomerType")->where("SYS.Value", '=', "Governments/Organizations")->paginate(100);
     
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
       // $getConnection = DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")
        // ->join("PowerSys.dbo.DATA_F_LOAD_PROFILE AS LP", "LP.MSNO", "FDAY.MSNO")
        // ->leftJoin("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
         //->leftJoin("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")
        // ->leftJoin("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
        // ->leftJoin("PowerSys.dbo.ACHV_POWERGRID AS PG", "PG.ID", "PNG.ID")
        // ->leftJoin("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
       //  ->leftJoin("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
       //  ->select("FDAY.MSNO", "FDAY.DATE", "FDAY.SAVEDB_TIME", "FDAY.BEGINTIME", "FDAY.ENDTIME", "FDAY.KWH_ABS", "FDAY.KWH_ABS_START", "FDAY.KWH_ABS_END", "PNG.Region", "PNG.BusinessHub", "PNG.Transformer", "SYS.Value AS AssetType")
       //  ->where("FDAY.MSNO", '=',  `$meterNo`)->paginate(30);



       $getConnection = DB::connection("ami")->table("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY")
       // ->join("PowerSys.dbo.DATA_F_LOAD_PROFILE AS LP", "LP.MSNO", "FDAY.MSNO")
        ->leftJoin("PowerSys.dbo.ACHV_METER AS MT", "MT.MSNO", "FDAY.MSNO")
        ->leftJoin("PowerSys.dbo.ACHV_POC AS POC", "MT.ID", "POC.Meter_ID")
        ->leftJoin("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
        ->leftJoin("PowerSys.dbo.ACHV_POWERGRID AS PG", "PG.ID", "PNG.ID")
        ->leftJoin("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
        ->leftJoin("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
        ->select("FDAY.MSNO", "FDAY.DATE", "FDAY.SAVEDB_TIME", "FDAY.BEGINTIME", "FDAY.ENDTIME", "FDAY.KWH_ABS", "FDAY.KWH_ABS_START", "FDAY.KWH_ABS_END", "PNG.Region", "PNG.BusinessHub", "PNG.Transformer", "SYS.Value AS AssetType")
        ->where("SYS.Tag", '=', 'CustomerType')->where("FDAY.MSNO", '=',  $meterNo)->paginate(30);
     
         return $getConnection;
    }


    public function getMeterReading($year, $month){
       
        $getConnection = DB::connection("ami")->table("PowerSys.dbo.ACHV_METER AS AC")
        ->leftJoin("PowerSys.dbo.ACHV_POC AS POC", "AC.ID", "POC.Meter_ID")
        ->leftJoin("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
        ->leftJoin("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")

        ->leftJoin("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY", "FDAY.MSNO", "AC.MSNO")
        ->leftJoin("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
        ->select("AC.MSNO",  DB::raw('CONVERT(Varchar(50), SUM(Cast(FDAY.KWH_ABS as money)),1) as consumption'))
        ->where("SYS.Tag", '=', 'CustomerType')
        ->whereYear("FDAY.BEGINTIME", $year)->whereMonth("FDAY.BEGINTIME", $month)
        //->groupBy('AC.MSNO', 'PNG.Region', 'FDAY.BEGINTIME', "PNG.BusinessHub", "PNG.Transformer", "FDAY.ENDTIME")
        ->groupBy('AC.MSNO')
        ->orderByRaw('CASE WHEN CONVERT(Varchar(50), SUM(Cast(FDAY.KWH_ABS as money)),1) IS NULL THEN 1 ELSE 0 END, CONVERT(Varchar(50), SUM(Cast(FDAY.KWH_ABS as money)),1) DESC')
        ->paginate(50);
      
        return $getConnection;
    }


    public function powerUppowerDown() {
        $getConnection = DB::connection("main_warehouse")->table("MAIN_WAREHOUSE.dbo.ami_power_up_down_View AS AC")->paginate(30);
        return $getConnection;
    }

    public function getMonthlySummary(){

        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        $getConnection =  DB::connection("ami")->table("PowerSys.dbo.ACHV_METER AS AC")
        ->leftJoin("PowerSys.dbo.ACHV_POC AS POC", "AC.ID", "POC.Meter_ID")
        ->leftJoin("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
        ->leftJoin("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY", "FDAY.MSNO", "AC.MSNO")
        ->leftJoin("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
        ->select("AC.MSNO", DB::raw('CONVERT(Varchar(50), SUM(Cast(FDAY.KWH_ABS as money)),1) as consumption'))
        ->where("SYS.Tag", '=', 'CustomerType')
        ->whereYear("FDAY.BEGINTIME", $currentYear)->whereMonth("FDAY.BEGINTIME", $currentMonth)
        ->groupBy('AC.MSNO')->paginate(500);

        $mainResult = AmiminiResource::collection($getConnection);

        return $mainResult;
    }

}
