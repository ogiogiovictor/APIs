<?php


namespace App\Services;
use App\Models\DimensionCustomer;
use App\Models\ZoneCustomer;
use App\Models\ECMIPayment;
use DB;
use Closure;
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
use Illuminate\Support\Facades\Auth;
use App\Services\GeneralService;
use App\Models\Customer\CustomerAuthModel;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\HighTension;
use App\Http\Resources\TransformerResource;
use App\Http\Resources\NewResource;
use App\Models\SubAccount;




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

        $user = Auth::user();
        $getSpecialRole =  (new GeneralService)->getSpecialRole();
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();

        if(isset($request->status) &&  $request->status != 'null'){

            $StatusCode =  substr($request->status, 0, 1);

            if(in_array($getUserRoleObject['role'], $getSpecialRole) && $user->isHQ()){
                $customers = DimensionCustomer::where("StatusCode", $StatusCode)
                ->where("AccountType", 'Postpaid')->orderBy("SetupDate", "desc")->paginate(20); 
            }else if($user->isRegion()){
                $customers = DimensionCustomer::where("StatusCode", $StatusCode)
                ->where("AccountType", 'Postpaid')->where("Region", $getUserRoleObject['region'])
                ->orderBy("SetupDate", "desc")->paginate(20); 
            }else if($user->isBhub()){
                $customers = DimensionCustomer::where("StatusCode", $StatusCode)
                ->where("AccountType", 'Postpaid')->where("Region", $getUserRoleObject['region'])
                ->where("BusinessHub", $getUserRoleObject['business_hub'])->orWhere("BUID", $getUserRoleObject['business_hub'])
                ->orderBy("SetupDate", "desc")->paginate(20); 
            }else if($user->isSCenter()){
                $customers = DimensionCustomer::where("StatusCode", $StatusCode)
                ->where("AccountType", 'Postpaid')->where("Region", $getUserRoleObject['region'])
                ->where("BusinessHub", $getUserRoleObject['business_hub'])->orWhere("BUID", $getUserRoleObject['business_hub'])
                ->where("service_center", $getUserRoleObject['sc'])
                ->orderBy("SetupDate", "desc")->paginate(20); 
            }
                
            } else{

            $customers = DimensionCustomer::whereIn('StatusCode', ['A', 'S'])
            ->where("Region", $getUserRoleObject['region'])->where("AccountType", $request->type)
            ->orderBy("SetupDate", "desc")->paginate(20); //getPostpaid
        }

       // $customers = DimensionCustomer::whereIn('StatusCode', ['A', 'S'])->where("AccountType", $requestType)->paginate(20);

        return CustomerResource::collection($customers)->response()->getData(true);

    }


    public function getPrepaid($request) {
        $user = Auth::user();
        $getSpecialRole =  (new GeneralService)->getSpecialRole();
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();
        
        if(isset($request->status) &&  $request->status != 'null'){

            $StatusCode =  substr($request->status, 0, 1);
            $map = [
                'A' => '1',
                'I' => '0',
                // add other mappings here if needed
            ];
            $StatusCode = $map[$StatusCode] ?? $StatusCode;

            if(in_array($getUserRoleObject['role'], $getSpecialRole) && $user->isHQ()){

                $customers = DimensionCustomer::where('StatusCode', $StatusCode)->where("AccountType", $request->type)
                ->orderBy("SetupDate", "desc")->paginate(20); //getPrepaid
            }else if($user->isRegion()) {
                $customers = DimensionCustomer::where('StatusCode', $StatusCode)->where("AccountType", $request->type)
                ->where("Region", $getUserRoleObject['region'])->orderBy("SetupDate", "desc")->paginate(20); //getPrepaid
            }else if($user->isBhub()) {
                $customers = DimensionCustomer::where('StatusCode', $StatusCode)->where("AccountType", $request->type)
                ->where("Region", $getUserRoleObject['region'])
                ->where("BusinessHub", $getUserRoleObject['business_hub'])->orWhere("BUID", $getUserRoleObject['business_hub'])
                ->orderBy("SetupDate", "desc")->paginate(20); //getPrepaid
            }else if($user->isSCenter()){
                $customers = DimensionCustomer::where('StatusCode', $StatusCode)->where("AccountType", $request->type)
                ->where("Region", $getUserRoleObject['region'])
                ->where("BusinessHub", $getUserRoleObject['business_hub'])->orWhere("BUID", $getUserRoleObject['business_hub'])
                ->where("service_center", $getUserRoleObject['sc'])->orderBy("SetupDate", "desc")->paginate(20); //getPrepaid
            }

        }else {
        
            $customers = DimensionCustomer::whereIn('StatusCode', ['0', '1'])
            ->where("Region", $getUserRoleObject['region'])->where("AccountType", $request->type)
            ->orderBy("SetupDate", "desc")->paginate(20); //getPrepaid
        }

       // $customers = DimensionCustomer::whereIn('StatusCode', ['0', '1'])->where("AccountType", $requestType)->paginate(20);

        return CustomerResource::collection($customers)->response()->getData(true);

    }


    public function getWarehouseDashboard() {

    $roleName = ['project_officer', 'billing', 'cfo', 'coo', 'admin', 'md', 'ami', 'audit', 'md', 'hcs', 'cco', 'it', 'cfo', 'coo'];

    $role_name = Auth::user()->roles->pluck('name')->first();
    $user = Auth::user();
    $checkLevel = Auth::user()->level;

    $getSpecialRole =  (new GeneralService)->getSpecialRole();
    $getUserRoleObject = (new GeneralService)->getUserLevelRole();

    if(in_array($getUserRoleObject['role'], $getSpecialRole) && $user->isHQ()){
        $getCustomerByRegion = DimensionCustomer::selectRaw('Region, count(*) as total')->groupBy('Region')->get();
    }else if($user->isRegion()){
        $getCustomerByRegion = DimensionCustomer::selectRaw('Region, count(*) as total')->where("Region", $getUserRoleObject['region'])->groupBy('Region')->get();
    }else if($user->isBhub()){
        $getCustomerByRegion = DimensionCustomer::selectRaw('Region, count(*) as total')
        ->where("Region", $getUserRoleObject['region'])
        ->where("BusinessHub", $getUserRoleObject['business_hub'])
        ->groupBy('Region')->get();
    }else if($user->isSCenter()){
        $getCustomerByRegion = DimensionCustomer::selectRaw('Region, count(*) as total')
        ->where("Region", $getUserRoleObject['region'])
        ->where("BusinessHub", $getUserRoleObject['business_hub'])
        ->where("service_center", $getUserRoleObject['sc'])
        ->groupBy('Region')->get();
    }

    

    $TotalCustomers =  $this->getLevel($roleName, $user, $checkLevel, $role_name);
    $TotalDSS = $this->getDSS(); //DTWarehouse::count();
    $TotalFeederEl = FeederEleven::count();
    $TotalFeederThirty =  FeederThirty::count();
    $TotalTickets = Tickets::count();
    $CustomerByRegion = $getCustomerByRegion;
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

    private function getDSS(){
        $user = Auth::user();
        $specialRole = (new GeneralService)->getSpecialRole();
        $dss = (new GeneralService)->getUserLevelRole();

        if(in_array($dss['role'], $specialRole) && $user->isHQ()){
            return DTWarehouse::count();
        }elseif($user->isBhub()) {
            return DTWarehouse::where("hub_name", $dss['business_hub'])->count();
        }
        //$dss['region'];
    }

    private function getLevel($roleName, $user, $checkLevel, $role_name){

        if(in_array($role_name, $roleName) && $user->isHQ()){
            return  $result =  DimensionCustomer::whereIn('statusCode', ['0', '1', 'A', 'S'])->count(); 
        }

        if (!empty($checkLevel)) {
            $values = explode(",", $checkLevel);  // ->where("service_center", $serviceCenter)
            $region = $values[0] ?? null;
            $businessHub = $values[1] ?? null;
            $serviceCenter = $values[2] ?? null;
       
              if($user->isRegion()){
                  $values = explode(",", $checkLevel);
                  $region = $values[0];
                  return $result =  DimensionCustomer::where('Region', $region)->whereIn('statusCode', ['0', '1', 'A', 'S'])->count(); 
              }else if($user->isBhub()){
                  $values = explode(",", $checkLevel);
                  $businessHub = $values[1];
                  return  $result =  DimensionCustomer::where('BusinessHub', $businessHub)->orWhere("BUID", $businessHub)->whereIn('statusCode', ['0', '1', 'A', 'S'])->count(); 
              }else if($user->isSCenter()){
                  $values = explode(",", $checkLevel);
                  $region = $values[0];
                  $businessHub = $values[1];
                  $serviceCenter = $values[2];
                  return $result =  DimensionCustomer::where('Region', $region)->where("BUID", $businessHub)->where("service_center", $serviceCenter)->whereIn('statusCode', ['0', '1', 'A', 'S'])->count(); 
              }else{
                  $values = explode(", ", $checkLevel);
                  $businessHub = $values[1];
                 return  $result =  DimensionCustomer::where('BusinessHub', $businessHub)->orWhere("BUID", $businessHub)->whereIn('statusCode', ['0', '1', 'A', 'S'])->count(); 
              }
        }
        
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
        
        $newFormatedNumber = StringHelper::formatAccountNumber($changeAccountNumber);
       
        if($accountType == 'Postpaid'){
            $customer = DimensionCustomer::with(['bills' => function ($query) {
                $query->orderByDesc('Billdate');
            }])->where('AccountNo', $newFormatedNumber)->first();
        }else {
            $customer = DimensionCustomer::with(['bills' => function ($query) {
                $query->orderByDesc('Billdate');
            }])->where('MeterNo', $MeterNo)->first();
        }

        if ($customer->AccountType == 'Postpaid') {
            //$customer->load('payments');
            $customer->load(['payments' => function ($query) {
                $query->orderBy('PayDate', 'desc');
            }]);
        } elseif ($customer->AccountType == 'Prepaid') {
            //$customer->load('transactions');
            $customer->load(['transactions' => function ($query) {
                $query->orderBy('TransactionDateTime', 'desc');  //[TransactionDateTime]
            }]);
        }

      
        if($dss && $dss != 'null'){
            $checkdss = DTWarehouse::where("Assetid", $dss)->value('Assetid');
            if($checkdss){
                $distribution = new TransformerResource(DTWarehouse::select('Assetid', 'assettype', 'AssetName', 'DSS_11KV_415V_Make',
                'DSS_11KV_415V_Rating', 'DSS_11KV_415V_Address', 'DSS_11KV_415V_Owner', 'hub_name', 'Status',
                'DSS_11KV_415V_parent', 'longtitude', 'latitude', 'naccode')->where('Assetid', $dss)->first());
                // $distribution = DTWarehouse::select('Assetid', 'assettype', 'AssetName', 'DSS_11KV_415V_Make',
                // 'DSS_11KV_415V_Rating', 'DSS_11KV_415V_Address', 'DSS_11KV_415V_Owner', 'hub_name', 'Status',
                // 'DSS_11KV_415V_parent', 'longtitude', 'latitude', 'naccode')->where('Assetid', $dss)->first();
                $customer->distribution = $distribution;
            }
        }
       

     
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

       if($accountType == 'Prepaid'){
        $subAccountBal = SubAccount::select("SubAccountNo", "AccountNo", "AmountAttached", "Balance", "SubAccountAbbre", "ModeOfPayment", "PaymentAmount", "lastmodified")
        ->where(["AccountNo" => $newFormatedNumber, "SubAccountAbbre" => 'OUTBAL'])->first();

            $addBalance = 0;
            if($subAccountBal){
                $subAccountBalFpUnit = SubAccount::where(["AccountNo" => $newFormatedNumber, "SubAccountAbbre" => 'FPUNIT'])->first()->Balance;
                $addBalance = $subAccountBal->Balance + $subAccountBalFpUnit;
                $subAccountBal->Balance = number_format($addBalance, 2, ".", "");
            }
        $customer->outbalance = $subAccountBal;
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
            'c.TarriffCode',
            'c.Region',
            'c.BUID',
            'c.BusinessHub',
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







/************************************* IBEDC ALTERNATE PAYMENT SYSTEM **************************************************/


    public function authenticateCustomers($meterNo, $accountType) {
        $customers = DimensionCustomer::where(function ($query) use ($meterNo, $accountType) {
            $query->where("MeterNo", $meterNo)
                  ->where("AccountType", $accountType);
        })->orWhere(function ($query) use ($meterNo, $accountType) {
            $query->where("AccountNo", $meterNo)
                  ->where("AccountType", $accountType);
        })->first();
    
        return $customers;
    }
    


    // public function authenticateCustomersOLD($meterNo, $accountType) {
    //     $customers =  DimensionCustomer::select('*')->where(function ($query) use ($meterNo, $accountType) {
    //         $query->whereNotIn("StatusCode", ["0, I, C, N"]);
    //         $query->where('AccountNo', 'like', '%'. $meterNo .  '%');
    //         $query->orWhere('MeterNo', $meterNo );
    //         $query->Where('AccountType', $accountType);
    //     })->first(); 

    //     return $customers;

    // }




    public function customerDetails($meterNo) {
        $customers =  DimensionCustomer::select('*')->where(function ($query) use ($meterNo) {
            $query->whereNotIn("StatusCode", ["0, I, C, N"]);
            $query->where('AccountNo', 'like', '%'. $meterNo .  '%');
            $query->orWhere('MeterNo', $meterNo );
        })->first(); 

        return $customers;

    }


    public function getHeaderRequest(Request $request)

    {
        $headerRq = $request->header('Authorization');

        $matches = ['Authorization' => $headerRq];
        $checkRequest =  CustomerAuthModel::where($matches)->get();

        if($checkRequest){

            return $checkRequest;
        }else {
            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }




}
