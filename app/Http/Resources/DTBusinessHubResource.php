<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
//use App\Models\Test\DimensionCustomer;
//use App\Models\Test\ZoneBills;
use Illuminate\Support\Facades\DB;
use App\Models\DimensionCustomer;
use App\Models\ZoneBills;
use App\Models\ZonePayments;
use App\Models\BusinessUnit;
use App\Models\ECMIPayment;
use Carbon\Carbon;


class DTBusinessHubResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        $previousMonth = date('n', strtotime('-1 month'));

        $previousMonth = Carbon::now()->subMonth()->format('m');
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        return [ 
            'hub_name' => $this->hub_name,
            'asset_count' => $this->asset_count,
            'customers' =>  number_format(DimensionCustomer::where('BusinessHub', $this->hub_name)->count("AccountNo"), 2),
            'prepaid_customers' =>  number_format(DimensionCustomer::where('BusinessHub', $this->hub_name)->where("AccountType", 'Prepaid')->count("BusinessHub"), 2),
            'postpaid_customers' =>  number_format(DimensionCustomer::where('BusinessHub', $this->hub_name)->where("AccountType", 'Postpaid')->count("BusinessHub"), 2),
            'postpaid_payments' =>  number_format(ZonePayments::where("BusinessUnit", $this->getBunit($this->hub_name))->where("PayYear", $currentYear)->where("payMonth", $previousMonth)->sum("Payments"), 2),
            /* 'prepaid_payments_current' =>  number_format(ECMIPayment::where("BUID", $this->hub_name)
                ->whereRaw("YEAR(TransactionDateTime) = YEAR(DATEADD(MONTH, -1, GETDATE()))")
                ->whereRaw("MONTH(TransactionDateTime) = MONTH(DATEADD(MONTH, -1, GETDATE()))")
                ->sum("Amount"), 2),
                */

            'prepaid_payments_current' =>   number_format(ECMIPayment::where("BUID", $this->hub_name)
                ->whereYear("TransactionDateTime", $currentYear)
                ->whereMonth("TransactionDateTime", $currentMonth)
                ->sum("Amount"), 2),

            'prepaid_payments_previous' =>  number_format(ECMIPayment::where("BUID", $this->hub_name)
            ->whereYear("TransactionDateTime", $currentYear)
            ->whereMonth("TransactionDateTime", $previousMonth)
            ->sum("Amount"), 2),
            
            'bills' => number_format(ZoneBills::where('BUName1', strtoupper($this->hub_name))->where('BillYear', $currentYear)->where('BillMonth', $previousMonth)->sum(DB::raw('CurrentChgTotal + Vat')), 2),

        ];
        //return parent::toArray($request);
    }


   

    



    


    private function getBunit($unitcode){
        return $businessUnit = BusinessUnit::where("Name", $unitcode)->value('BUID');
        
    }
}
