<?php

namespace App\Services;
use App\Models\DimensionCustomer;
use App\Models\ZoneCustomer;
use App\Models\ECMICustomer;
use Illuminate\Support\Facades\DB;
use App\Models\ZoneBills;
use App\Helpers\StringHelper;
use Carbon\Carbon;



class BillService
{
   

    public function getBills($currentMonth, $currentYear)
    {
        $thisMonthBills = ZoneBills::where('BillMonth', $currentMonth)
            ->where('BillYear', $currentYear)
            ->sum('CurrentChgTotal');

        $lastMonth = Carbon::now()->subMonth()->month;

        $lastMonthBill = ZoneBills::where('BillMonth', $lastMonth)
            ->where('BillYear', $currentYear)
            ->sum('CurrentChgTotal');


        // $topCustomers = ZoneBills::where('BillMonth', $lastMonth)
        //     ->where('BillYear', $currentYear)
        //     ->orderByDesc('CurrentChgTotal', 'desc')
        //     ->sum('CurrentChgTotal');

            $topCustomers = ZoneBills::where('BillMonth', $lastMonth)
            ->where('BillYear', $currentYear)
            ->orderByDesc('CurrentChgTotal')
            ->take(100)
            ->get()
            ->sum('CurrentChgTotal');

    
        

        $totalAmountOwed = DimensionCustomer::selectRaw('SUM(b.GrandTotaldue - b.Payment) AS TotalAmountOwed')
            ->from('main_warehouse.dbo.Dimension_customers as c')
            ->join('MAIN_WAREHOUSE.dbo.FactBill as b', 'c.AccountNo', '=', 'b.AccountNo')
            ->whereRaw('b.GrandTotaldue * 0.2 > b.Payment')
            ->where('b.BillYear', '=',  $currentYear)
            ->where('b.BillMonth', '=', $currentMonth)
            ->where('AccountType', 'Postpaid')
            ->value('TotalAmountOwed');
        

       $bills  = ZoneBills::orderBy("BillYear", "desc")->paginate(30);

      return  $data = [
            'thisMonthBills' => naira_format($thisMonthBills),
            'lastMonthBills' => naira_format($lastMonthBill),
            'bills' => $bills,
           'totalHighestBill' => $totalAmountOwed,
            'highestBilledCustomers' => naira_format($topCustomers),
        ];
        

         //return $data;
    }
}
