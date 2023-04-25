<?php

namespace App\Services;
use App\Models\DimensionCustomer;
use App\Models\ZoneCustomer;
use DB;
use App\Models\DTWarehouse;
use App\Models\FeederEleven;
use App\Models\FeederThirty;
use App\Models\Tickets;
use App\Http\Resources\CustomerResource;
use App\Models\CRMUser;
use App\Models\MsmsCustomer;



class CustomerService
{
    public function __construct()
    {
        //
    }

    public function getCustomerInfo(){
        $customers = DimensionCustomer::select('SetupDate', 'AccountNo', 'BookNo', 'MeterNo', 'Mobile', 'OldAccountNo', 'TariffID', 'Surname', 'FirstName', 'OtherNames', 'AcctTypeDesc',
        'OldTariffCode', 'TarriffCode', 'AccountType', 'Address', 'BUID', 'BusinessHub', 'service_center', 'UTID',
        'ConnectionType', 'ArrearsBalance', 'State', 'City', 'StatusCode')->whereIn("StatusCode", ['A', 'S', '1', '0'])->orderBy('SetupDate', 'desc')->paginate(20);

      
        return CustomerResource::collection($customers)->response()->getData(true);

    }

    public function findCustomer($search_term) {
        $customers =  DimensionCustomer::select('*')->where(function ($query) use ($search_term ) {
            $query->whereNotIn("StatusCode", ["0, I, C, N"]);
            //$query->where('AccountNo', $search_term);
            $query->where('AccountNo', 'like', '%'. $search_term .  '%');
            $query->orWhere('MeterNo', $search_term );
           // $query->orWhere('OldAccountNo', $search_term);
        })->get();  //first();

        return $customers;

    }


    public function getPostpaid($requestType) {

        $customers = DimensionCustomer::whereIn('StatusCode', ['A', 'S'])->where("AccountType", $requestType)->paginate(20);

        return CustomerResource::collection($customers)->response()->getData(true);

    }


    public function getPrepaid($requestType) {
        
        $customers = DimensionCustomer::whereIn('StatusCode', ['0', '1'])->where("AccountType", $requestType)->paginate(20);

        return CustomerResource::collection($customers)->response()->getData(true);

    }


    public function getWarehouseDashboard() {

    $TotalCustomers = DimensionCustomer::whereIn('statusCode', ['0', '1', 'A', 'S'])->count();
    $TotalDSS = DTWarehouse::count();
    $TotalFeederEl = FeederEleven::count();
    $TotalFeederThirty =  FeederThirty::count();
    $TotalTickets = Tickets::count();
    $CustomerByRegion = DimensionCustomer::selectRaw('Region, count(*) as total')->groupBy('Region')->get();
    $recentCustomers = CustomerResource::collection(DimensionCustomer::whereIn('statusCode', ['0', '1', 'A', 'S'])->orderBy('SetupDate', 'desc')->take(10)->get());


    $data = [
        'total_dss' => StringHelper::formatNumber($TotalDSS),
        'total_customers' => StringHelper::formatNumber($TotalCustomers), //DB::connection('stagging')->table("ems_customers")->count(),
        'feeder_11' => StringHelper::formatNumber($TotalFeederEl), //DB::connection('stagging')->table("gis_11KV Feeder")->count(),
        'feeder_33' => StringHelper::formatNumber($TotalFeederThirty), //DB::connection('stagging')->table("gis_33KV Feeder")->count(),
       'crm_tickets' => StringHelper::formatNumber($TotalTickets),  //DB::connection('crm')->table("tickets")->count(), // Access denied issue to be fixed by infrastructure  //$TotalTickets
        'customer_by_region' => StringHelper::formatNumber($CustomerByRegion),
        'recent_customers' => $recentCustomers,
        "total_staff" => 0,
        "outsourced_staff" => 0,
        "msms_meters" => 0,
        "service_centers" => 0,
    ];

    return $data;
    

    }


    public function getCustomerByType(){

        $postpaid = DimensionCustomer::selectRaw('StatusCode, count(*) as total')
        ->where("AccountType", 'Postpaid')->groupBy('StatusCode')->get();

        $prepaid = DimensionCustomer::selectRaw('StatusCode, count(*) as total')
        ->where("AccountType", 'Prepaid')->groupBy('StatusCode')->get();

        $data = [
            'postpaid' => $customers,
            'prepaid' => $prepaid,
        ];
        
        return $data;
    }


    public function customer360($changeAccountNumber, $dss){

        $customer = DimensionCustomer::with('bills')->where('AccountNo', $changeAccountNumber)->first();

        if ($customer->AccountType == 'Postpaid') {
            $customer->load('payments');
        } elseif ($customer->AccountType == 'Prepaid') {
            $customer->load('transactions');
        }

      
        if($dss){
            $distribution = DTWarehouse::select('Assetid', 'assettype', 'AssetName', 'DSS_11KV_415V_Make',
            'DSS_11KV_415V_Rating', 'DSS_11KV_415V_Address', 'DSS_11KV_415V_Owner', 
            'DSS_11KV_415V_parent', 'longtitude', 'latitude', 'naccode')->where('Assetid', $dss)->first();
            $customer->distribution = $distribution;
        }
       

       $crm_user = CRMUser::where('accountno', $changeAccountNumber)->first();

       if($crm_user){
        //Get the Tickets
       $tickets = Tickets::where('user_id', $crm_user->id)->get();
       $customer->tickets = $tickets;
       }
       

       $msmsMeters = MsmsCustomer::with(['customer_meters', 'meter_details'])
       ->select("id", "title", "surname", "firstname", "other_names", "supply_address",
       "lga", "contact_no", "email", "means_of_id", "o_account_no", "service_center", "unique_code",
       "debt", "debt_date", "debt_type")
       ->where('o_account_no', $changeAccountNumber)->first();
       if($msmsMeters){
        $customer->msmsCustomerInfo = $msmsMeters;
       }

      

       return $customer;


    }



}
