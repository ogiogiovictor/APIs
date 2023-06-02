<?php

namespace App\Services;
use App\Models\DimensionCustomer;
use App\Models\ZoneCustomer;
use App\Models\ECMIPayment;
use DB;
use App\Models\DTWarehouse;
use App\Models\FeederEleven;
use App\Models\FeederThirty;
use App\Models\Tickets;
use App\Http\Resources\CustomerResource;
use App\Models\CRMUser;
use App\Models\MsmsCustomer;
use App\Helpers\StringHelper;
use App\Services\AmiService;
use App\Models\ZoneBills;
use App\Models\MsmsMeters;
use App\Models\ServiceUnit;



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


    public function getPostpaid($request) {

        if(isset($request->status) &&  $request->status != 'null'){

            $StatusCode =  substr($request->status, 0, 1);

            $customers = DimensionCustomer::where("StatusCode", $StatusCode)
            ->where("AccountType", 'Postpaid')->orderBy("SetupDate", "desc")->paginate(20); 
                
            } else{

            $customers = DimensionCustomer::whereIn('StatusCode', ['A', 'S'])->where("AccountType", $request->type)
            ->orderBy("SetupDate", "desc")->paginate(20); //getPostpaid
        }

       // $customers = DimensionCustomer::whereIn('StatusCode', ['A', 'S'])->where("AccountType", $requestType)->paginate(20);

        return CustomerResource::collection($customers)->response()->getData(true);

    }


    public function getPrepaid($request) {
        
        if(isset($request->status) &&  $request->status != 'null'){

            $StatusCode =  substr($request->status, 0, 1);
            $map = [
                'A' => '1',
                'I' => '0',
                // add other mappings here if needed
            ];
            $StatusCode = $map[$StatusCode] ?? $StatusCode;

            $customers = DimensionCustomer::where('StatusCode', $StatusCode)->where("AccountType", $request->type)
            ->orderBy("SetupDate", "desc")->paginate(20); //getPrepaid

        }else {
        
        $customers = DimensionCustomer::whereIn('StatusCode', ['0', '1'])->where("AccountType", $request->type)
        ->orderBy("SetupDate", "desc")->paginate(20); //getPrepaid
        }

       // $customers = DimensionCustomer::whereIn('StatusCode', ['0', '1'])->where("AccountType", $requestType)->paginate(20);

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
        'total_dss' => number_format_short($TotalDSS),
        'total_customers' => number_format_short($TotalCustomers), //DB::connection('stagging')->table("ems_customers")->count(),
        'feeder_11' => ($TotalFeederEl), //DB::connection('stagging')->table("gis_11KV Feeder")->count(),
        'feeder_33' => ($TotalFeederThirty), //DB::connection('stagging')->table("gis_33KV Feeder")->count(),
       'crm_tickets' => number_format_short($TotalTickets),  //DB::connection('crm')->table("tickets")->count(), // Access denied issue to be fixed by infrastructure  //$TotalTickets
       'customer_by_region' => $CustomerByRegion,
       'recent_customers' => $recentCustomers,
       "total_staff" => 0,
       "outsourced_staff" => 0,
       "msms_meters" => number_format_short(MsmsMeters::count()),
       "service_centers" => number_format_short(ServiceUnit::count()),
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


    public function customer360($changeAccountNumber, $dss, $accountType, $MeterNo){

       
        //$customer = DimensionCustomer::with('bills')->where('AccountNo', $changeAccountNumber)->first();
        if($accountType == 'Postpaid'){
            $customer = DimensionCustomer::with(['bills' => function ($query) {
                $query->orderByDesc('Billdate');
            }])->where('AccountNo', $changeAccountNumber)->first();
        }else {
            $customer = DimensionCustomer::with(['bills' => function ($query) {
                $query->orderByDesc('Billdate');
            }])->where('MeterNo', $MeterNo)->first();
        }

        if ($customer->AccountType == 'Postpaid') {
            $customer->load('payments');
        } elseif ($customer->AccountType == 'Prepaid') {
            $customer->load('transactions');
        }

      
        if($dss){
            $distribution = DTWarehouse::select('Assetid', 'assettype', 'AssetName', 'DSS_11KV_415V_Make',
            'DSS_11KV_415V_Rating', 'DSS_11KV_415V_Address', 'DSS_11KV_415V_Owner', 'hub_name', 'Status',
            'DSS_11KV_415V_parent', 'longtitude', 'latitude', 'naccode')->where('Assetid', $dss)->first();
            $customer->distribution = $distribution;
        }
       

       $newFormatedNumber = StringHelper::formatAccountNumber($changeAccountNumber);
       $crm_user = CRMUser::where('accountno', $newFormatedNumber)->first();

       if($crm_user){
        //Get the Tickets
        $tickets = Tickets::where('user_id', $crm_user->id)->get();
        $customer->tickets = $tickets;
       }
       

       $msmsMeters = MsmsCustomer::with(['customer_meters', 'meter_details'])
       ->select("id", "title", "surname", "firstname", "other_names", "supply_address",
       "lga", "contact_no", "email", "means_of_id", "o_account_no", "service_center", "unique_code",
       "debt", "debt_date", "debt_type")
       ->where('o_account_no', $newFormatedNumber)->first();
       
       if($msmsMeters){
        $customer->msmsCustomerInfo = $msmsMeters;
       }

       //Use the account number to return the meter number
       $getMeterNo = DimensionCustomer::where('AccountNo', $newFormatedNumber)->value('MeterNo');

       $amiEvents = (new AmiService)->getAmiReading($getMeterNo);
       if($amiEvents){
        $customer->amiEvents = $amiEvents;
       }

       //Get Disconnections
       if($changeAccountNumber){
        $year =  Date('Y');
        $month = Date('m') -1;
        $disconnections =  DimensionCustomer::select(
            'c.AccountNo',
            'c.Surname',
            'c.FirstName',
            'c.OtherNames',
            'c.AccountType',
            'b.TotalDue',
            'b.GrandTotaldue',
            'b.Payment',
            DB::raw('(b.GrandTotaldue - b.Payment) AS AmountOwed'),
            DB::raw('b.GrandTotaldue * 0.2 as ExpectedPayment'),
            'b.BillYear',
            'b.BillMonth'
        )
            ->from('main_warehouse.dbo.Dimension_customers as c')
            ->join('MAIN_WAREHOUSE.dbo.FactBill as b', 'c.AccountNo', '=', 'b.AccountNo')
            ->whereRaw('b.GrandTotaldue * 0.2 > b.Payment')
            ->where('b.AccountNo', '=',  $newFormatedNumber)
            ->where('b.BillYear', '=',  $year)
            ->where('b.BillMonth', '=', $month)
            ->where('AccountType', 'Postpaid')
            ->first();
            $customer->disconnections = $disconnections;
       }
      

      

       return $customer;


    }



    public function getDisconnections(){
        $year =  Date('Y');
        $month = Date('m');

      

        $results = DimensionCustomer::select(
            'c.AccountNo',
            'c.Surname',
            'c.FirstName',
            'c.OtherNames',
            'c.AccountType',
            'b.TotalDue',
            'b.GrandTotaldue',
            'b.Payment',
            DB::raw('(b.GrandTotaldue - b.Payment) AS AmountOwed'),
            DB::raw('b.GrandTotaldue * 0.2 as ExpectedPayment'),
            'b.BillYear',
            'b.BillMonth'
        )
            ->from('main_warehouse.dbo.Dimension_customers as c')
            ->join('MAIN_WAREHOUSE.dbo.FactBill as b', 'c.AccountNo', '=', 'b.AccountNo')
            ->whereRaw('b.GrandTotaldue * 0.2 > b.Payment')
            ->where('b.BillYear', '=',  $year)
            ->where('b.BillMonth', '=', $month)
            ->where('AccountType', 'Postpaid')
            ->paginate(200);
    
        
            return   $results;
        

      
        
    }



}
