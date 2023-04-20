<?php

namespace App\Services;
use App\Models\DimensionCustomer;
use App\Models\ZoneCustomer;
use App\Models\ECMICustomer;
use DB;
use App\Models\ZoneBills;
use App\Helpers\StringHelper;




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

        //Top 100 Highest Billed Customers
        $topCustomers = DimensionCustomer::with(['zoneBills' => function($query) {
            $query->select('AccountNo', DB::raw('SUM(CurrentChgTotal) as total_billed'))
                ->groupBy('AccountNo');
        }])
        ->select('AccountNo')
        //->orderByDesc('spectrumbill.total_billed')
        ->take(100)
        ->get();

        $totalBilled = $topCustomers->sum(function($customer) {
            return $customer->zoneBills->sum('total_billed');
        });


        $bills  = ZoneBills::paginate(30);

        $data = [
            'thisMonthBills' => StringHelper::formatNumber($thisMonthBills),
            'lastMonthBills' => StringHelper::formatNumber($lastMonthBill),
            'bills' => $bills,
            'totalHighestBill' => StringHelper::formatNumber($totalBilled),
            'highestBilledCustomers' => $topCustomers,
        ];
        

         return $data;
    }
}
