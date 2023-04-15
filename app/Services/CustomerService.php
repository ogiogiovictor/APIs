<?php

namespace App\Services;
use App\Models\DimensionCustomer;
use App\Models\ZoneCustomer;
use DB;
use App\Models\DTWarehouse;
use App\Models\FeederEleven;
use App\Models\FeederThirty;
use App\Models\Tickets;




class CustomerService
{
    public function __construct()
    {
        //
    }

    public function getCustomerInfo(){
        $customers = DimensionCustomer::select('SetupDate', 'AccountNo', 'BookNo', 'MeterNo', 'Mobile', 'OldAccountNo', 'TariffID', 'Surname', 'FirstName', 'OtherNames', 'AcctTypeDesc',
        'OldTariffCode', 'TarriffCode', 'AccountType', 'Address', 'BUID', 'BusinessHub', 'service_center', 'UTID',
        'ConnectionType', 'ArrearsBalance', 'State', 'City', 'StatusCode')->whereIn("StatusCode", ['A', 'S', '1', '0'])->paginate(15);

        return $customers;
    }

    public function findCustomer($search_term) {
        $customers =  DimensionCustomer::select('*')->where(function ($query) use ($search_term ) {
            $query->whereNotIn("StatusCode", ["P, I, C, N"]);
            $query->where('AccountNo', $search_term);
            //$query->where('AccountNo', 'like', '%'. $search_term .  '%');
            $query->orWhere('MeterNo', $search_term );
           // $query->orWhere('OldAccountNo', $search_term);
        })->get();  //first();

        return $customers;

    }


    public function getPostpaid($requestType) {

        $customers = DimensionCustomer::whereIn('StatusCode', ['A', 'S'])->where("AccountType", $requestType)->paginate(20);

        return $customers;

    }


    public function getPrepaid($requestType) {
        
        $customers = DimensionCustomer::whereIn('StatusCode', ['0', '1'])->where("AccountType", $requestType)->paginate(20);

        return $customers;

    }


    public function getWarehouseDashboard() {

    $TotalCustomers = DimensionCustomer::whereIn('statusCode', ['0', '1', 'A', 'S'])->count();
    $TotalDSS = DTWarehouse::count();
    $TotalFeederEl = FeederEleven::count();
    $TotalFeederThirty =  FeederThirty::count();
    $TotalTickets = Tickets::count();
    $CustomerByRegion = DimensionCustomer::selectRaw('Region, count(*) as total')->groupBy('Region')->get();

    $data = [
        'total_dss' => $TotalDSS,
        'total_customers' => $TotalCustomers, //DB::connection('stagging')->table("ems_customers")->count(),
        'feeder_11' => $TotalFeederEl, //DB::connection('stagging')->table("gis_11KV Feeder")->count(),
        'feeder_33' => $TotalFeederThirty, //DB::connection('stagging')->table("gis_33KV Feeder")->count(),
       'crm_tickets' => $TotalTickets,  //DB::connection('crm')->table("tickets")->count(), // Access denied issue to be fixed by infrastructure  //$TotalTickets
        'customer_by_region' => $CustomerByRegion
    ];

    return $data;
    

    }
}
