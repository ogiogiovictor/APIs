<?php

namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Test\DimensionCustomer;
use App\Models\Test\DTWarehouse;
use App\Models\Test\FeederEleven;
use App\Models\Test\FeederThirty;
use App\Models\Test\Tickets;
use App\Enums\AssetEnum;
use App\Enums\FeederEnum;
use App\Http\Resources\UserResource;
use App\Http\Resources\CustomerResource;
use App\Helpers\StringHelper;
use App\Models\Test\ZoneBills;
use App\Models\Test\ZonePayment;
use App\Models\Test\ECMIPayment;
use App\Models\Test\CRMUsers;
use Illuminate\Support\Facades\Validator;
use App\Models\Test\MsmsCustomer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\TicketRequest;
use App\Http\Requests\RecordRequest;
use App\Http\Resources\EcmiPaymentResource;
use App\Models\Test\TransmissionStation;
use App\Models\Test\ServiceUnit;
use App\Models\Test\MsmsMeters;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\FeederRequest;
use App\Helpers\AssetHelper;
use App\Exports\DataExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;



class TestController extends BaseApiController
{
    public function login(LoginRequest $request) //: Object
    {

        if($request->expectsJson()) {

            $userStatus = User::where('email', $request->email)->value('status');


            if($userStatus == 0 ){ //|| $userStatus == 'NULL'
                return $this->sendError('Invalid User', "No Activation Included in the account.  $userStatus", Response::HTTP_UNAUTHORIZED);
            }

            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $authUser = Auth::user();
                $success['Authorization'] = $authUser->createToken('Sanctom+Socialite')->plainTextToken;
                $success['user'] = $authUser;
                return $this->sendSuccess($success, "Authorization Successufully Generated", Response::HTTP_CREATED);
            }else {
                return $this->sendError('Invalid Login', "Check your credentials and try again", Response::HTTP_UNAUTHORIZED);
            }

        }else {
            return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }


    public function stats() {

        //$customers = (new CustomerService)->getWarehouseDashboard();  // Production

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

        return $this->sendSuccess($data, "Asset Information Saved Successfully", Response::HTTP_OK);

    }

    public function getUser(){

        if(!Auth::check()) {
            return $this->sendError("No Data", "Error Loading User Data", Response::HTTP_UNAUTHORIZED);
        }

        try{
            return $authUser = new UserResource(Auth::user()); 
        }catch(\Exception $e) {
            return $this->sendError("No Data", "Error Loading User Data", Response::HTTP_UNAUTHORIZED);
        }
       
        //return $this->sendSuccess($user, "User Information", Response::HTTP_OK);
    }


    public function allCustomers(Request $request){

          // $postpaid = DimensionCustomer::selectRaw('StatusCode, count(*) as total')
        // ->where("AccountType", 'Postpaid')->orderBy("SetupDate", "desc")->groupBy('StatusCode')->get();

        $postpaid = DimensionCustomer::selectRaw('
        CASE 
            WHEN StatusCode = "A" THEN "Active"
            WHEN StatusCode = "C" THEN "Close"
            WHEN StatusCode = "I" THEN "Inactive"
            WHEN StatusCode = "S" THEN "Suspended"
        END AS StatusCode,
        COUNT(*) AS total')
        ->where("AccountType", 'Postpaid')
        ->orderBy("SetupDate", "desc")
        ->groupBy('StatusCode')
        ->get();

        // $prepaid = DimensionCustomer::selectRaw('StatusCode, count(*) as total')
        // ->where("AccountType", 'Prepaid')->orderBy("SetupDate", "desc")->groupBy('StatusCode')->get();

        $prepaid = DimensionCustomer::selectRaw('
        CASE 
            WHEN StatusCode = "1" THEN "Active"
            WHEN StatusCode = "0" THEN "Inactive"
        END AS StatusCode,
        COUNT(*) AS total')
        ->where("AccountType", 'Prepaid')
        ->orderBy("SetupDate", "desc")
        ->groupBy('StatusCode')
        ->get();

        if($request->type == 'Postpaid'){

            if(isset($request->status) &&  $request->status != 'null'){

            $StatusCode =  substr($request->status, 0, 1);

            $customers = DimensionCustomer::where("StatusCode", $StatusCode)
            ->where("AccountType", 'Postpaid')->orderBy("SetupDate", "desc")->paginate(20); 
                
            } else{
            $customers = DimensionCustomer::whereIn('StatusCode', ['A', 'S'])->where("AccountType", $request->type)
            ->orderBy("SetupDate", "desc")->paginate(20); //getPostpaid
            }
           
           $data = [
            'customers' => CustomerResource::collection($customers)->response()->getData(true),
            'postpaid' => $postpaid,
            'prepaid' => $prepaid,
           ];

           
            return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

        } else if($request->type == 'Prepaid'){

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
            
            $data = [
                'customers' => CustomerResource::collection($customers)->response()->getData(true),
                'postpaid' => $postpaid,
                'prepaid' => $prepaid,
               ];

            return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

        }else {

            $customers = DimensionCustomer::select('SetupDate', 'DistributionID', 'CustomerSK', 'AccountNo', 'BookNo', 'MeterNo', 'Mobile', 'OldAccountNo', 'TariffID', 'Surname', 'FirstName', 'OtherNames', 'AcctTypeDesc',
            'OldTariffCode', 'TarriffCode', 'AccountType', 'Address', 'BUID', 'BusinessHub', 'service_center', 'UTID',
            'ConnectionType', 'ArrearsBalance', 'State', 'City', 'StatusCode')->whereIn("StatusCode", ['A', 'S', '1', '0'])
            ->orderBy("SetupDate", "desc")->paginate(20); //getAll

            $data = [
                'customers' => CustomerResource::collection($customers)->response()->getData(true),
                'postpaid' => $postpaid,
                'prepaid' => $prepaid,
               ];

            return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

        }

    }


    public function getAssetWH(Request $request){

        //11kva DT count
        //33kva DT count
        $elevenDt = DTWarehouse::where('assettype', AssetEnum::DT_eleven()->value)->count();
        $thirtyDt = DTWarehouse::where('assettype', AssetEnum::DT_thirty_three()->value)->count();
        $dtTotal = DTWarehouse::count();
        //$dtByBhub = DTWarehouse::selectRaw('BusinessHub, count(*) as total')->groupBy('BusinessHub')->get();

        //return $request->type . '--'. AssetEnum::DT_eleven()->value;

        if($request->type == 'Distribution Sub Station 11KV_415V'){
        
            $getDTs = DTWarehouse::withCount('getCustomerCount')
             ->where('assettype', AssetEnum::DT_eleven()->value)
             ->paginate(20);


            $data =[
                'allDt' => $getDTs,
                'elevenDt' => number_format_short($elevenDt),
                'thirtyDt' => number_format_short($thirtyDt),
                'dtTotal' => number_format_short($dtTotal),
               
            ];

            return $this->sendSuccess($data, "DSS Successfully Loaded", Response::HTTP_OK);

        } else if($request->type == AssetEnum::DT_thirty_three()->value) {
            
            $getDTs = DTWarehouse::withCount('getCustomerCount')
            ->where('assettype', AssetEnum::DT_thirty_three()->value)
            ->paginate(20);

            $data =[
                'allDt' => $getDTs,
                'elevenDt' => number_format_short($elevenDt),
                'thirtyDt' => number_format_short($thirtyDt),
                'dtTotal' => number_format_short($dtTotal),
            ];

            return $this->sendSuccess($data, "DSS Successfully Loaded", Response::HTTP_OK);
            
        }else {

            $getDTs = DTWarehouse::withCount('getCustomerCount')->paginate(20);

            $data =[
                'allDt' => $getDTs,
                'elevenDt' => number_format_short($elevenDt),
                'thirtyDt' => number_format_short($thirtyDt),
                'dtTotal' => number_format_short($dtTotal),
            ];

            return $this->sendSuccess($data, "DSS Successfully Loaded", Response::HTTP_OK);
            
        }

    }



    public function findex(Request $request){

        $elevenA = FeederEleven::where("assettype", FeederEnum::FT_eleven()->value)->count();
        $thirtyA = Feederthirty::where("assettype", FeederEnum::FT_thirty_three()->value)->count();
        $total = FeederEleven::count() + Feederthirty::count();

        $eleven = FeederEleven::select('11KV feeder.Assetid', 'serviceunits.Name', 'serviceunits.Biz_Hub',  'serviceunits.Region', '11KV feeder.naccode', '11KV feeder.assettype', '11KV feeder.Capture DateTime', 
        '11KV feeder.Synced DateTime', '11KV feeder.latitude', '11KV feeder.longtitude', '11KV feeder.F11kvFeeder_Name', '11KV feeder.F11kvFeeder_parent')
        ->leftjoin('gis_dss', '11KV feeder.F11kvFeeder_parent', '=', 'gis_dss.Assetid')
        ->leftjoin('serviceunits', 'gis_dss.DSS_11KV_415V_Owner', '=', 'serviceunits.Name')
        ->orderByDesc('11KV feeder.Capture DateTime');;
        

        $thirty = Feederthirty::select('33KV feeder.Assetid', 'serviceunits.Name',  'serviceunits.Biz_Hub', 'serviceunits.Region', '33KV feeder.naccode', '33KV feeder.assettype', '33KV feeder.Capture DateTime',
         '33KV feeder.Synced DateTime', '33KV feeder.latitude', '33KV feeder.longtitude', '33KV feeder.F33kv_Feeder_Name', '33KV feeder.F33kv_Feeder_parent')
        ->leftjoin('gis_dss', '33KV feeder.F33kv_Feeder_parent', '=', 'gis_dss.Assetid')
        ->leftjoin('serviceunits', 'gis_dss.DSS_11KV_415V_Owner', '=', 'serviceunits.Name');
        //->orderByDesc('33KV feeder.Capture DateTime');
        

        $feeders = $eleven->unionAll($thirty)->paginate(20);

        $data = [
           'feeder_eleven' => $elevenA,
           'feeder_thirty' => $thirtyA,
           'total_feeder' => $total,
           'feeders' => $feeders,
        ];

        return $this->sendSuccess($data, "Feeder Successfully Loaded", Response::HTTP_OK);

        

    }


    public function getPayments(){

        $newpayment = new EcmiPayment();
        $ecmi_payment = $newpayment->paymentCount();
        $ems_payment = ZonePayment::count();
        $total_payments = $ecmi_payment + $ems_payment;
        $today_payment_ecmi = $newpayment->whereDate('TransactionDateTime', Carbon::today())->count();
        $today_payment_ems = ZonePayment::whereDate('PayDate', Carbon::today())->count();

        $selectECMI = ECMIPayment::select("TransactionDateTime", "BUID", "TransactionNo", "Token", 
        "AccountNo", "MeterNo", "Amount",  DB::raw("'prepaid' as CSPClientID"), DB::raw("REGEXP_REPLACE(AccountNo, '[^0-9a-zA-Z]+', '') as FAccount"))
        ->orderBy("TransactionDateTime", "desc");

       $selectEMS = ZonePayment::select("PayDate", "BusinessUnit", "PaymentID", "receiptnumber", 
       "AccountNo", "MeterNo", "Payments",  DB::raw("'postpaid' as PaymentSource"), DB::raw("REGEXP_REPLACE(AccountNo, '[^0-9a-zA-Z]+', '') as FAccount"))
       ->orderBy("PayDate", "desc");

       $bothpayment = $selectECMI->unionAll($selectEMS)->orderBy('TransactionDateTime', 'desc')->paginate(20);

       
        $data = [
            'ecmi_payment' => naira_format($ecmi_payment),
            'ems_payment' => naira_format($ems_payment),
            'total_payments' => naira_format($total_payments),
            'payments' => $bothpayment,
            'today_payments' => naira_format($today_payment_ecmi + $today_payment_ems),
        ];

        return $this->sendSuccess($data, "Payment Successfully Loaded", Response::HTTP_OK);
    }


    public function getPaymentDetails($account, $Token, $CSPClientID){
        //$formattedAccount = StringHelper::formatAccountNumber($account);
         //   return  $formattedAccount;
        if($CSPClientID == "prepaid"){
           // $payment = ECMIPayment::where('AccountNo', $formattedAccount)->where('Token', $Token)->first();
            $payment = new EcmiPaymentResource(ECMIPayment::where('Token', $Token)->first());
          
        }else {
            //$payment = ZonePayment::where('AccountNo', $account)->where('receiptnumber', $Token)->first();
            $payment = new ZoneResource(ZonePayment::where('receiptnumber', $Token)->first());
        }

        return $this->sendSuccess($payment, "Payment Successfully Loaded", Response::HTTP_OK);
    }


    public function tindex() {
        $tickets = Tickets::paginate(20);

        $closedTicket = Tickets::where('status', 'closed')->count();
        $openTickets = Tickets::where('status', 'open')->count();
        $unassignedTickets = Tickets::where('unassigned', 1)->count();

        $data = [
            'tickets' => $tickets,
            'totalTicket' => number_format_short($tickets->count()),
            'closedTicket' => number_format_short($closedTicket),
            'openTicket' => number_format_short($openTickets),
            'unassigned' => number_format_short($unassignedTickets),
        ];

        if($tickets){
            return $this->sendSuccess($data, "Customer 360 Loaded", Response::HTTP_OK);
        }else {
            return $this->sendError("No Data", "No data Found" , Response::HTTP_NO_CONTENT);
        }
    }


    
    public function tshow(TicketRequest $request){


        $ticket = Tickets::where('ticket_no', $request->ticketid)->first();

        if(!$ticket){
            return $this->sendError("No Data", "No Ticket Found" , Response::HTTP_NO_CONTENT);
        }
        //Get the Customer Details in CRM
        $getAccountNo = CRMUsers::where('id', $ticket->user_id)->first();
       // Now Get the Customer Information.
        $customer = DimensionCustomer::where('AccountNo', $getAccountNo->accountno)
        ->orWhere('MeterNo', $getAccountNo->accountno)->first();

        $data = [
            'ticket' => $ticket,
            'customer' => $customer,
            'totalTicket' => $ticket->count(),
        ];

        if($ticket){
            return $this->sendSuccess($data, "Tickets and Customer Information Fully Loaded", Response::HTTP_OK);
        }else {
            return $this->sendError("No Data", "No data Found" , Response::HTTP_NO_CONTENT);
        } 

    }


    public function customer360($acctionNo, $dss){

        try {

           $changeAccountNumber = StringHelper::formatAccountNumber($acctionNo);

           // $customer = DimensionCustomer::with('bills')->where('AccountNo', $changeAccountNumber)->first();

            $customer = DimensionCustomer::with(['bills' => function ($query) {
                $query->orderByDesc('Billdate');
            }])->where('AccountNo', $changeAccountNumber)->first();

            if ($customer->AccountType == 'Postpaid') {
                //$customer->load('payments');
                $customer->load(['payments' => function ($query) {
                    $query->orderBy('PayDate', 'desc');
                }]);
            } elseif ($customer->AccountType == 'Prepaid') {
               // $customer->load('transactions');
                $customer->load(['payments' => function ($query) {
                    $query->orderBy('TransactionDateTime', 'desc');
                }]);
            }

            
           

            if($dss){
                $distribution = DTWarehouse::select('Assetid', 'assettype', 'AssetName', 'DSS_11KV_415V_Make',
                'DSS_11KV_415V_Rating', 'DSS_11KV_415V_Address', 'DSS_11KV_415V_Owner', 
                'DSS_11KV_415V_parent', 'longtitude', 'latitude', 'naccode')->where('Assetid', $dss)->first();
                $customer->distribution = $distribution;
            }

            $crm_user = CRMUsers::where('accountno', $changeAccountNumber)->first();

            if($crm_user){
            //Get the Tickets
             $tickets = Tickets::where('user_id', $crm_user->id)->get();
             $customer->tickets = $tickets;
            }
          
             //Get the MSMS Meters
            /* $msmsMeters = MsmsCustomer::with('meter_details')->select("id", "title", "surname", "firstname", "other_names", "supply_address",
             "lga", "contact_no", "email", "means_of_id", "o_account_no", "service_center", "unique_code",
             "debt", "debt_date", "debt_type")->where('o_account_no', $changeAccountNumber)->first();
            */

            $msmsMeters = MsmsCustomer::with(['customer_meters', 'meter_details'])
            ->select("id", "title", "surname", "firstname", "other_names", "supply_address",
            "lga", "contact_no", "email", "means_of_id", "o_account_no", "service_center", "unique_code",
            "debt", "debt_date", "debt_type")
            ->where('o_account_no', $changeAccountNumber)->first();
            
            if($msmsMeters){
                $customer->msmsCustomerInfo = $msmsMeters;
            }
           


            return $this->sendSuccess($customer, "Customer 360 Loaded", Response::HTTP_OK);

        }catch(\Exception $e) {
            return $this->sendError("No Data", $e , Response::HTTP_NOT_FOUND);
        }

    }


    public function getBills(){

        try{

             $currentMonth = Carbon::now()->month;
             $currentYear = Carbon::now()->year;
   
            $thisMonthBills = ZoneBills::where('BillMonth', $currentMonth)
            ->where('BillYear', $currentYear)
            ->sum('CurrentChgTotal');

            $lastMonth = Carbon::now()->subMonth()->month;

            $lastMonthBill = ZoneBills::where('BillMonth', $lastMonth)
            ->where('BillYear', $currentYear)
            ->sum('CurrentChgTotal');

            
            //Top 100 Highest Billed Customers
            $topCustomers = DimensionCustomer::with(['zoneBills' => function($query) {
                $query->orderBy('BillDate', 'desc')
                ->select('AccountNo', DB::raw('SUM(CurrentChgTotal) as total_billed'))
                    ->groupBy('AccountNo');
            }])
            ->select('AccountNo')
            ->take(100)
            ->get();

            $totalBilled = $topCustomers->sum(function($customer) {
                return $customer->zoneBills->sum('total_billed');
            });


            $bills  = ZoneBills::orderBy('BillDate', 'desc')->paginate(30);

            $data = [
                'thisMonthBills' => naira_format($thisMonthBills),
                'lastMonthBills' => naira_format($lastMonthBill),
                'bills' => $bills,
                'totalHighestBill' => naira_format($totalBilled),
                'highestBilledCustomers' => $topCustomers,
            ];
            
            return $this->sendSuccess($data, "Bills Loaded", Response::HTTP_OK);
        }catch(\Exception $e) {
            return $this->sendError("No Bills", $e , Response::HTTP_UNAUTHORIZED);
        }
    }

    public function getTransmissionStations(){
            
            try{
    
                $transmissionStations = TransmissionStation::all();
    
                return $this->sendSuccess($transmissionStations, "Transmission Stations Loaded", Response::HTTP_OK);
            }catch(\Exception $e) {
                return $this->sendError("No Transmission Stations", $e , Response::HTTP_UNAUTHORIZED);
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


    public function cstore(RecordRequest $request){
       
        $response = Http::post('http://localhost:8001/api/v1/post_customer_crmd', $request->all());

        try{
            if($response['data'] == '201'){
                return $this->sendSuccess($response->json(), "Customer Created", Response::HTTP_OK);
            }else{
                return $this->sendError("Error", $response->json() , Response::HTTP_UNAUTHORIZED);
            }

        }catch(\Exception $err){
            return $this->sendError("Error", $err , Response::HTTP_UNAUTHORIZED);
        }
        
        
    }
    public function getCrmd(){

        $response = Http::get('http://localhost:8001/api/v1/get_customers');
        $data = $response->json();

        return $this->sendSuccess($data, "CRMD Loaded", Response::HTTP_OK);

    }


    public function storeAsset(AssetRequest $request){
       
        // DTWarehouse::create($request->all());
        try {
            $request['AssetName'] = $request['DSS_11KV_415V_Name'];

            $assetData = AssetHelper::dataRequest($request);

            $storeAsset =  DTWarehouse::create($assetData);

          //  $storeAsset =  $this->AssetRepository->storeDTs($assetData, $request->assettype);
            return $this->sendSuccess($storeAsset, "Asset Information Saved Successfully", Response::HTTP_OK);

        }catch(\Exception $e){
            return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }
       
    }


    public function updateStatus(Request $request){
        
       // $array =  $request->all();
        //$array['userid'] = 1; //this will be the person logged in
     
        try{

            $response = Http::post('http://localhost:8001/api/v1/update_crmd_doc', $request->all());
           
            if($response){
                return $this->sendSuccess($response, "Customer CRMD Approved Successfully", Response::HTTP_OK);
            }
         

        }catch(\Exception $e){
            return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }

     
    }


    public function addFeeder(FeederRequest $request){

        $request['AssetName'] = $request['F11kvFeeder_Name'];
        $assetData = AssetHelper::dataRequest($request);

        if($request->assettype == "11KV Feeder"){

            $createFeeder = FeederEleven::create($assetData);
            
        }else {

            $createFeeder = FeederThirty::create([
                'F33kv_Feeder_Name' => $assetData['F33kv_Feeder_Name'],
                'assettype' => $assetData['assettype'],
                'latitude' => $assetData['latitude'],
                'longtitude' => $assetData['longtitude'],
                'naccode' => $assetData['naccode'],
                'F33kv_Regional_Name' => $assetData['F33kv_Regional_Name'],
                'F33kv_Business_Hub_Name' => $assetData['F33kv_Business_Hub_Name'],
                'F33kV_Feeder_Circuit_Breaker_Make' => $assetData['F33kV_Feeder_Circuit_Breaker_Make'],
                'F33kV_Feeder_Circuit_Breaker_Type' => $assetData['F33kV_Feeder_Circuit_Breaker_Type'],
                'F33kV_Upriser_Cable_Type' => $assetData['F33kV_Upriser_Cable_Type'],
                'F33kv_Teeoffs' => $assetData['F33kv_Teeoffs'],
                'F33kv_Tee_offs_Coordinate' => $assetData['F33kv_Tee_offs_Coordinate'],
                'F33kv_Substations_capacity' => $assetData['F33kv_Substations_capacity'],
                'F33kv_lineload_coordinate' => $assetData['F33kv_lineload_coordinate'],
                'F33kv_Conductor_Size' => $assetData['F33kv_Conductor_Size'],
                'F33kv_Aluminium_Conductor' => $assetData['F33kv_Aluminium_Conductor'],
                'F33kv_Commisioning' => $assetData['F33kv_Commisioning'],
            ]);

        }

        return $this->sendSuccess($createFeeder, "Asset Information Saved Successfully", Response::HTTP_OK);
    }



    public function customerByStatus($status, $postpaid='Postpaid') {

        $postpaid = DimensionCustomer::selectRaw('
        CASE 
            WHEN StatusCode = "A" THEN "Active"
            WHEN StatusCode = "C" THEN "Close"
            WHEN StatusCode = "I" THEN "Inactive"
            WHEN StatusCode = "S" THEN "Suspended"
        END AS StatusCode,
        COUNT(*) AS total')
        ->where("AccountType", 'Postpaid')
        ->orderBy("SetupDate", "desc")
        ->groupBy('StatusCode')
        ->get();
            
        $StatusCode =  substr($status, 0, 1);

        $customers = DimensionCustomer::where("StatusCode", $StatusCode)
        ->where("AccountType", 'Postpaid')->orderBy("SetupDate", "desc")->paginate(20); //getPostpaid

        $data = [
         'customers' => CustomerResource::collection($customers)->response()->getData(true),
         'postpaid' => $postpaid
        ];

        
         return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);
    }



    public function searchRecords(Request $request){
        
        if($request->hiddenField == "dt_asset"){
            $searchQuery = $request->searchQuery;

            $search = DTWarehouse::where(function ($query) use ($request) {
                $searchQuery = $request->searchQuery;

                $query->where('DSS_11KV_415V_Name', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('Assetid', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('DSS_11KV_415V_Owner', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('DSS_11KV_415V_Address', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('hub_name', 'LIKE', '%' .  $searchQuery . '%');
            })->paginate(100);

            $elevenDt = DTWarehouse::where(function ($query) use ($request) {
                $searchQuery = $request->searchQuery;

                $query->where('DSS_11KV_415V_Name', 'LIKE', '%' .  $searchQuery . '%')
                ->where('assettype', AssetEnum::DT_eleven()->value)
                ->orWhere('Assetid', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('DSS_11KV_415V_Owner', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('DSS_11KV_415V_Address', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('hub_name', 'LIKE', '%' .  $searchQuery . '%');
            })->count();

            $thirtyDt = DTWarehouse::where(function ($query) use ($request) {
                $searchQuery = $request->searchQuery;
                $query->where('DSS_11KV_415V_Name', 'LIKE', '%' .  $searchQuery . '%')
                ->where('assettype', AssetEnum::DT_thirty_three()->value)
                ->orWhere('Assetid', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('DSS_11KV_415V_Owner', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('DSS_11KV_415V_Address', 'LIKE', '%' .  $searchQuery . '%')
                ->orWhere('hub_name', 'LIKE', '%' .  $searchQuery . '%');
            })->count();

        $dtTotal = DTWarehouse::count();

            $data =[
                'allDt' => $search,
                'elevenDt' => $elevenDt,
                'thirtyDt' => $thirtyDt,
                'dtTotal' => $dtTotal,
            ];

            return $this->sendSuccess($data, "Search Asset Succesfuly Found", Response::HTTP_OK);
        }else {
            return $this->sendError("No Result", "No Result Found", Response::HTTP_BAD_REQUEST);
        }
    }   



    public function exportExcel(Request $request){

        if($request->has('export_dt')){
            $startDate = $request->start_date;
            $startEndData = $request->end_date;
            $BusinessHub = $request->business_hub;
        }

        $fileName = 'data.xlsx';

        $export = new DataExport();
    
        $path = storage_path('app/public/exported-files/' . $fileName);
    
        Excel::store($export, $path);
    
        $downloadLink = Storage::url('public/exported-files/' . $fileName);
    
        return $this->sendSuccess($downloadLink, "download_link", Response::HTTP_OK);
     
       
       
    }


    public function getAllUsers(){

        $users = User::paginate(20);
        // Modify the date format and status values
        $users->getCollection()->transform(function ($user) {
            // Convert created_at to human-readable date format
            $user->created_at = Carbon::parse($user->created_at)->format('Y-m-d H:i:s');
           // $user->created_at = Carbon::parse($user->created_at)->diffForHumans();

            // Convert status values to human-readable strings
            $user->status = $user->status == 1 ? 'Active' : 'Inactive';

            return $user;
        });

        return $this->sendSuccess($users, "Users Loaded", Response::HTTP_OK);
    }


    public function addUser(Request $request){

       

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users|max:255',
            'password' => 'required',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation Error", $validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'status' => 1,
            'authority' => $request->business_hub,
            'password' => Hash::make($request->password),
        ]);

          //Atach User to a Role
          $user->assignRole('admin');

        return $this->sendSuccess($user, "User Created Successfully", Response::HTTP_OK);
    }

    



}
