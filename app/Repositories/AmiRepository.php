<?php

namespace App\Repositories;

use App\Repositories\AmiRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\AmiminiResource;

class AmiRepository implements AmiRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search(){

       $search_term =  $this->request->MonthlyEvent;
       $year =   $this->request->year ?? Date('Y');
       $month =  $this->request->month ?? Date('m');

       $getConnection = DB::connection("ami")->table("PowerSys.dbo.ACHV_METER AS AC")
       ->leftJoin("PowerSys.dbo.ACHV_POC AS POC", "AC.ID", "POC.Meter_ID")
       ->leftJoin("PowerSys.dbo.ACHV_CUSTOMER AS CUS", "CUS.ID", "POC.Customer_ID")
       ->leftJoin("PowerSys.dbo.ACHV_POWERGRID_NAME AS PNG", "PNG.ID", "POC.PowerGrid_ID")
       ->leftJoin("PowerSys.dbo.ACHV_POWERGRID AS PG", "PG.ID", "PNG.ID")
       ->leftJoin("PowerSys.dbo.DATA_F_DPS_DAY AS FDAY", "FDAY.MSNO", "AC.MSNO")
       ->leftJoin("PowerSys.dbo.SYS_BASE AS SYS", "SYS.Key", "CUS.CustomerType")
       ->select("AC.MSNO", "PG.Descr", DB::raw('CONVERT(Varchar(50), SUM(Cast(FDAY.KWH_ABS as money)),1) as consumption'))
       ->where("SYS.Tag", '=', 'CustomerType')->where("AC.MSNO", '=', $search_term)
       ->whereYear("FDAY.BEGINTIME", $year)->whereMonth("FDAY.BEGINTIME", $month)
       //->groupBy('AC.MSNO', 'PNG.Region', 'FDAY.BEGINTIME', "PNG.BusinessHub", "PNG.Transformer", "FDAY.ENDTIME")
       ->groupBy('AC.MSNO', 'PG.Descr')
       ->orderByRaw('CASE WHEN CONVERT(Varchar(50), SUM(Cast(FDAY.KWH_ABS as money)),1) IS NULL THEN 1 ELSE 0 END, CONVERT(Varchar(50), SUM(Cast(FDAY.KWH_ABS as money)),1) DESC')
       ->get();

       return  AmiminiResource::collection($getConnection);


    }

}
