<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\DimensionCustomer;
use App\Models\ZoneBills;
use Illuminate\Support\Facades\DB;

class BillingEfficencyResource extends JsonResource
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
        $lastMonth = date('n', strtotime('-1 month'));


        $customers = DimensionCustomer::whereIn('DistributionID', $this->Assetid)->pluck('AccountNo')->toArray();
        if(count($customers) > 0){
           
            $bills = ZoneBills::whereIn('AccountNo', $customers)->where('BillYear', $currentYear)->where('BillMonth', $currentMonth)->sum(DB::raw('CurrentChgTotal + Vat'));
            $payments = ZoneBills::whereIn('AccountNo', $customers)->where('BillYear', $currentYear)->where('BillMonth', $currentMonth)->sum('Payments');
            $customerCountInZoneBills = ZoneBills::whereIn('AccountNo', $customers)->where('BillYear', $currentYear)->where('BillMonth', $currentMonth)->distinct('AccountNo')->count();
            
        }


        return [
            "Assetid" => $this->Assetid,
            "AccountNo" => $this->AccountNo,
            "DSS_11KV_415V_Name" => $this->DSS_11KV_415V_Name,
            "hub_name" => $this->hub_name,
            "Assetid" => $this->Assetid,
            "totalcustomer_in_dss" => count($customers),
            "billed_customers" => $customerCountInZoneBills,
            "unbilled_customers" => count($customers) - $customerCountInZoneBills,
            "total_billed" => $bills,
            "total_payments" => $payments,

        ];
        //return parent::toArray($request);
    }
}
