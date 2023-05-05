<?php

namespace App\Http\Controllers\Bills;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\BillService;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ZoneBills;

class CustomerBills extends BaseApiController
{
    public function getBills(){ 

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;


        try{

            $bills = (new BillService)->getBills($currentMonth, $currentYear);
            return $this->sendSuccess($data, "Bills Loaded", Response::HTTP_OK);

        }catch(\Exception $e) {

            return $this->sendError("No Bills", $e , Response::HTTP_UNAUTHORIZED);
        }

    }


    public function getBillDetails($billID){

        try{
    
            $billDetails = ZoneBills::where('BillID', $billID)->first();

            return $this->sendSuccess($billDetails, "Bill Details Loaded", Response::HTTP_OK);
        }catch(\Exception $e) {
            return $this->sendError("No Bill Details", $e , Response::HTTP_UNAUTHORIZED);
        }
    }
}
