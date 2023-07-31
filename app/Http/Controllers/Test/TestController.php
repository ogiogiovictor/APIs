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
use Spatie\Permission\Models\Role;
use App\Models\MenuRole;
use App\Models\SubMenu;
use App\Models\MenuAccess;
use App\Models\Meters;
use App\Services\GeneralService;
use App\Services\AssetService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Resources\DTBusinessHubResource;
use App\Models\AssignSubMenu;
use App\Imports\CAADImport;
use App\Models\BulkCAAD;
use App\Models\FileCAAD;
use App\Models\ProcessCAAD;
use App\Http\Requests\CaadRequest; 
use App\Enums\CaadEnum;
use App\Models\Caad;
use App\Models\CAADCommentApproval;
use Illuminate\Support\Facades\Password;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Jobs\RegistrationJob;

use App\Http\Resources\CAADResource;



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
        $user = Auth::user();
        $getSpecialRole =  (new GeneralService)->getSpecialRole();
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();

        if(in_array($getUserRoleObject['role'], $getSpecialRole) && $user->isHQ()){

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

        }else if($user->isRegion()){

            $postpaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = "A" THEN "Active"
                WHEN StatusCode = "C" THEN "Close"
                WHEN StatusCode = "I" THEN "Inactive"
                WHEN StatusCode = "S" THEN "Suspended"
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Postpaid')
            ->where("Region", $getUserRoleObject['region'])
            ->orderBy("SetupDate", "desc")
            ->groupBy('StatusCode')
            ->get();
    
            $prepaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = "1" THEN "Active"
                WHEN StatusCode = "0" THEN "Inactive"
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Prepaid')
            ->where("Region", $getUserRoleObject['region'])
            ->orderBy("SetupDate", "desc")
            ->groupBy('StatusCode')
            ->get();

        }else if($user->isBhub()){

            $postpaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = "A" THEN "Active"
                WHEN StatusCode = "C" THEN "Close"
                WHEN StatusCode = "I" THEN "Inactive"
                WHEN StatusCode = "S" THEN "Suspended"
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Postpaid')
            ->where("Region", $getUserRoleObject['region'])
            ->where("BusinessHub", $getUserRoleObject['business_hub'])->orWhere("BUID", $getUserRoleObject['business_hub'])
            ->orderBy("SetupDate", "desc")
            ->groupBy('StatusCode')
            ->get();
    
            $prepaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = "1" THEN "Active"
                WHEN StatusCode = "0" THEN "Inactive"
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Prepaid')
            ->where("Region", $getUserRoleObject['region'])
            ->where("BusinessHub", $getUserRoleObject['business_hub'])->orWhere("BUID", $getUserRoleObject['business_hub'])
            ->orderBy("SetupDate", "desc")
            ->groupBy('StatusCode')
            ->get();
        }else if($user->isSCenter()){

            $postpaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = "A" THEN "Active"
                WHEN StatusCode = "C" THEN "Close"
                WHEN StatusCode = "I" THEN "Inactive"
                WHEN StatusCode = "S" THEN "Suspended"
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Postpaid')
            ->where("Region", $getUserRoleObject['region'])
            ->where("BusinessHub", $getUserRoleObject['business_hub'])
            ->where("service_center", $getUserRoleObject['sc'])
            ->orderBy("SetupDate", "desc")
            ->groupBy('StatusCode')
            ->get();
    
            $prepaid = DimensionCustomer::selectRaw('
            CASE 
                WHEN StatusCode = "1" THEN "Active"
                WHEN StatusCode = "0" THEN "Inactive"
            END AS StatusCode,
            COUNT(*) AS total')
            ->where("AccountType", 'Prepaid')
            ->where("Region", $getUserRoleObject['region'])
            ->where("BusinessHub", $getUserRoleObject['business_hub'])
            ->where("service_center", $getUserRoleObject['sc'])
            ->orderBy("SetupDate", "desc")
            ->groupBy('StatusCode')
            ->get();
        }


        

        if($request->type == 'Postpaid'){

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
                $customers = DimensionCustomer::whereIn('StatusCode', ['0', '1'])->where("AccountType", $request->type)
                ->where("Region", $getUserRoleObject['region'])->orderBy("SetupDate", "desc")->paginate(20); //getPrepaid
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
            'ConnectionType', 'ArrearsBalance', 'State', 'City', 'StatusCode')
            ->where("Region", $getUserRoleObject['region'])->whereIn("StatusCode", ['A', 'S', '1', '0'])
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


    public function customer360($acctionNo, $dss, $accountType, $MeterNo){

        try {

            if($accountType == 'Postpaid'){
                $changeAccountNumber = StringHelper::formatAccountNumber($acctionNo);
                $customer = DimensionCustomer::with(['bills' => function ($query) {
                    $query->orderByDesc('Billdate');
                }])->where('AccountNo', $changeAccountNumber)->first();
            }else {
                $changeAccountNumber = $MeterNo;
                $customer = DimensionCustomer::with(['bills' => function ($query) {
                    $query->orderByDesc('Billdate');
                }])->where('MeterNo', $changeAccountNumber)->first();
            }

            // $customer = DimensionCustomer::with(['bills' => function ($query) {
            //     $query->orderByDesc('Billdate');
            // }])->where('AccountNo', $changeAccountNumber)->first();

            if ($customer->AccountType == 'Postpaid') {
                //$customer->load('payments');
                $customer->load(['payments' => function ($query) {
                    $query->orderBy('PayDate', 'desc');
                }]);
            } elseif ($customer->AccountType == 'Prepaid') {
               // $customer->load('transactions');
                $customer->load(['transactions' => function ($query) {
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
            $searchQuery = $request->DT;

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

        if($request->has('download') && $request->download == "download_customer"){

          
            $data = DimensionCustomer::whereBetween("SetupDate", [$request->start_date, $request->end_date])
            ->where("AccountType", $request->account_type)->where("BusinessHub", $request->business_hub)->where("Region", $request->Region)->get();


            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');
    
                // Write CSV headers
                fputcsv($file, ['AccountNo', 'Surname', 'DistributionID']);
    
                // Write data rows
                foreach ($data as $row) {
                    fputcsv($file, [$row->AccountNo, $row->Surname, $row->DistributionID]);
                }
    
                fclose($file);
            };
    
            // Set the response headers
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="export.csv"',
            ];

             // Return the streamed response
             return new StreamedResponse($callback, 200, $headers);

        }

        /*
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
        */
     
       
       
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

       
        if(!$request->user_id){
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
                'status' => "1",
                'authority' => $request->authority,
                'password' => Hash::make($request->password),
                'level' => $request->level ?? []
            ]);
    
              //Atach User to a Role
              $user->assignRole($request->role);
        }else {

            $validator = Validator::make($request->all(), [
               // 'name' => 'required',
               // 'email' => 'required|unique:users|max:255',
                'role' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError("Validation Error", $validator->errors(), Response::HTTP_BAD_REQUEST);
            }

            $user = User::find($request->user_id);
            //$user->name = $request->name;
            //$user->email = $request->email;
            $user->status = isset($request->status) ? $request->status :  0;
            $user->authority = isset($request->authority) ? $request->authority : null;
            $user->password = Hash::make($request->password);
            $user->level = isset($request->level) ? $request->level : '';
            $user->save();

            //Atach User to a Role
            $user->assignRole($request->role);
        }
       

       

       

          dispatch(new RegistrationJob($user, $request->password));

        return $this->sendSuccess($user, "User Created Successfully", Response::HTTP_OK);
    }


    public function getAccess() {

        //$getRoles = Role::all();
        //  return $this->sendSuccess($getRoles, "All Roles", Response::HTTP_OK);
        $userRole = Auth::user()->roles->pluck('id')->first();

        //Check if the user Role have access to the menu/submenu
        $menuRole = MenuRole::where('role_id', $userRole)->get();
        $userResource = new UserResource(Auth::user());
        $userResource->menuAccess($userRole);

        $menuRole = MenuRole::where('role_id', $userRole)->first()->menu_id;
        $menuString = array_map('intval', explode(',', trim($menuRole, '[]')));

      // $hasAccess = SubMenu::whereIn('menu_id', $menuString)->get();
        $hasAccess = SubMenu::whereIn("menu_id", $menuString)->where("role_id", $userRole)->get();

      //  $hasAccess = SubMenu::whereIn('menu_id', $menuString)->exists();


        return $this->sendSuccess($hasAccess, "Successfully", Response::HTTP_OK);
    }


    public function addNewCustomer(Request $request){
       
        try{

            $baseUrl = env('CUSTOMER_API_URL');
            $addCustomerUrl = $baseUrl . 'add_customer';

           return $response = Http::post($addCustomerUrl, $request->all());


        }catch(\Exception $e){
            return $e->getmessage();
        }
    }

    public function pendingCustomer(){

        $user = Auth::user();

        $baseUrl = env('CUSTOMER_API_URL');
        $addCustomerUrl = $baseUrl . 'pending_customers';

        try{

            if ($user->isHQ()) { 
                $filters = [
                    'status' => 'pending'
                ];
                $response = Http::get( $addCustomerUrl, $filters);
            } else if ($user->isRegion()) {
                $checkLevel = Auth::user()->level;
                $values = explode(", ", $checkLevel);
                $desiredValue = $values[0];
                $filters = [
                    'region' => $desiredValue,
                    'status' => 'pending'
                ];

                $response = Http::get( $addCustomerUrl, $filters);
            } else if ($user->isBhub()) {
                $checkLevel = Auth::user()->level;
                $values = explode(", ", $checkLevel);
                $desiredValue = $values[1];
                $filters = [
                    'business_hub' => $desiredValue,
                    'status' => 'pending'
                ];
                $response = Http::get( $addCustomerUrl, $filters);
            }  else if ($user->isSCenter()) {
                $checkLevel = Auth::user()->level;
                $values = explode(", ", $checkLevel);
                $desiredValue = $values[2];
                $filters = [
                    'service_center' =>  $filters,
                    'status' => 'pending'
                ];
                $response = Http::get( $addCustomerUrl, $filters);
            } 

            return $response;

        }catch(\Exception $e){
            return $e->getmessage();
        }
    }





    public function updateCustomer(Request $request){
      
         try{

            $baseUrl = env('CUSTOMER_API_URL');
            $addCustomerUrl = $baseUrl . 'update_customers_approve';
 
             $response = Http::post($addCustomerUrl, $request->all());
            
             if($response){
                 return $this->sendSuccess($response, "Customer Approved Successfully", Response::HTTP_OK);
             }
          
 
         }catch(\Exception $e){
             return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
         }

     }
 
    
     public function AccessControl() {
        $getMenu = MenuAccess::where("menu_status", "on")->get();
       // $getSubMenu = SubMenu::all();
        $getSubMenu = AssignSubMenu::all();

        $array = [];
        foreach($getMenu as $get){
            $array[] = [
                'menu_id' => $get->id,
                'menu_name' => $get->menu_name,
                'menu_status' => $get->menu_status,
                'submenu' => SubMenu::where('menu_id', $get->id)->get(),
               // 'submenu' => $this->refactorOutput(AssignSubMenu::where('menu_id', $get->id)->get())
            ];
        }

        return $this->sendSuccess($array, "Customer Approved Successfully", Response::HTTP_OK);
     }



     public function getRolePermission($role_id) {

       // $hasAccess = SubMenu::where("role_id", $role_id)->get();
        $hasAccess =  $this->refactorOutput(AssignSubMenu::where("role_id", $role_id)->get()); //AssignSubMenu::where("role_id", $role_id)->get();

        return $this->sendSuccess($hasAccess, "Successfully", Response::HTTP_OK);
    }


    private function refactorOutput($data){
        if($data){
            $array = [];
            foreach($data as $get){
                $array[] = [
                    'created_at' => $get->created_at,
                    'id' => intval($get->sub_menu_id),
                    'menu_id' => intval($get->menu_id),
                    'sub_menu_id' => intval($get->sub_menu_id),
                    'name' => SubMenu::where("id", $get->sub_menu_id)->value("name"),

                    'menu_status' => SubMenu::where("id", $get->sub_menu_id)->value("menu_status"),
                    'menu_url' =>SubMenu::where("id", $get->sub_menu_id)->value("menu_url"),
                   
                    'role_id' =>   $get->role_id
                ];
            }
            return $array;
        }

     }



    public function getMeter(){
        $getMeter = Meters::orderby('created_at', 'desc')->paginate(30);
        return $this->sendSuccess($getMeter, "All Meters", Response::HTTP_OK);
    }



    public function addMeter(Request $request){
        

        if($request->type){

            $addMeters = Meters::create([
                'type' => $request->type,
                'region' => isset($request->mdata['region']) ? $request->mdata['region'] : '',
                'business_hub' => isset($request->mdata['business_hub']) ? $request->mdata['business_hub'] : '',
                'transmission_station' => isset($request->mdata['transmission_station']) ? $request->mdata['transmission_station'] : '',
                '33feederline' => isset($request->mdata['33feederline']) ? $request->mdata['33feederline'] : '',
                'injection_substation' => isset($request->mdata['injection_substation']) ? $request->mdata['injection_substation'] : '',
                'address' => isset($request->mdata['address']) ? $request->mdata['address'] : '',
                'xformer_name' => isset($request->mdata['xformer_name']) ? $request->mdata['xformer_name'] :'',
                'distribution_xformer' => isset($request->mdata['distribution_xformer']) ? $request->mdata['distribution_xformer'] : '',
                'dss_name' => isset($request->mdata['dss_name']) ? $request->mdata['dss_name'] : '',
                'voltage_ratio' => isset($request->mdata['voltage_ratio']) ? $request->mdata['voltage_ratio'] : '',
                'dss_public_private' => isset($request->mdata['dss_public_private']) ? $request->mdata['dss_public_private'] : '',
                'latitude' => isset($request->mdata['latitude']) ? $request->mdata['latitude'] : '',
                'longitude' => isset($request->mdata['longitude']) ? $request->mdata['longitude'] : '',
                'meter_number' => isset($request->mdata['meter_number']) ? $request->mdata['meter_number'] : '',
                'meter_model' => isset($request->mdata['meter_model']) ? $request->mdata['meter_model'] : '',
                'meter_rated_capacity' => isset($request->mdata['meter_rated_capacity']) ? $request->mdata['meter_rated_capacity'] : '',
                'installation_capacity' => isset($request->mdata['installation_capacity']) ? $request->mdata['installation_capacity'] : '',
                'sim_serial_no' => isset($request->mdata['sim_serial_no']) ? $request->mdata['sim_serial_no'] : '',
                'network_provider' => isset($request->mdata['network_provider']) ? $request->mdata['network_provider'] : '',
                'vendor' => isset($request->mdata['vendor']) ? $request->mdata['vendor'] : '',
                'installation_date' => isset($request->mdata['installation_date']) ? $request->mdata['installation_date'] : '',
                'remarks' => isset($request->mdata['remarks']) ? $request->mdata['remarks'] : '',
                'sub_station' => isset($request->mdata['sub_station']) ? $request->mdata['sub_station'] : '',
                'feeder_name' => isset($request->mdata['feeder_name']) ? $request->mdata['feeder_name'] : '',
                'feeder_category' => isset($request->mdata['feeder_category']) ? $request->mdata['feeder_category'] :  '',
                'feeder_band' => isset($request->mdata['feeder_band']) ? $request->mdata['feeder_band'] : '',
                'feeder_type' => isset($request->mdata['feeder_type']) ? $request->mdata['feeder_type'] : '',
                'meter_make' => isset($request->mdata['meter_make']) ? $request->mdata['meter_make'] : '',
                'ct_ratio' => isset($request->mdata['ct_ratio']) ? $request->mdata['ct_ratio'] :  '',
                'pt_ratio' => isset($request->mdata['pt_ratio']) ? $request->mdata['pt_ratio'] : '',
                'account_number' => isset($request->mdata['account_number']) ? $request->mdata['account_number'] :'',
                'meter_rating' => isset($request->mdata['meter_rating']) ? $request->mdata['meter_rating'] : '',
                'meter_type' => isset($request->mdata['meter_type']) ? $request->mdata['meter_type'] : '',
                'category' => isset($request->mdata['category']) ? $request->mdata['category'] :'',
                'customer_name' => isset($request->mdata['customer_name']) ? $request->mdata['customer_name'] : '',
                'phone_number' => isset($request->mdata['phone_number']) ? $request->mdata['phone_number'] : '',
                'nature_of_business' => isset($request->mdata['nature_of_business']) ?? '',
                'tariff ' => isset($request->mdata['tariff']) ? $request->mdata['tariff'] : '',
                'service_band' => isset($request->mdata['service_band']) ? $request->mdata['service_band'] : '',
                'contact_person' => isset($request->mdata['contact_person']) ? $request->mdata['contact_person'] : '',
                'account_name' => isset($request->mdata['account_name']) ? $request->mdata['account_name'] : '',
                'contact_person_email' => isset($request->mdata['contact_person_email']) ? $request->mdata['contact_person_email'] :  '',
                'contact_person_address' => isset($request->mdata['contact_person_address']) ? $request->mdata['contact_person_address'] : '',
                'contact_person_phone' => isset($request->mdata['contact_person_phone']) ? $request->mdata['contact_person_phone'] : '',
                'initial_reading' => isset($request->mdata['initial_reading']) ? $request->mdata['initial_reading'] : '',

            ]);

            return $this->sendSuccess($addMeters, "Successfully", Response::HTTP_OK);

        }
            
        return $this->sendError("Error", "No Result Found", Response::HTTP_BAD_REQUEST);
        

        

    }

    public function getCustomerRegion($region)
    {
        $region = DimensionCustomer::where('Region', $region)->paginate(40);
        $allCustomer = CustomerResource::collection($region)->response()->getData(true);
        return $this->sendSuccess($allCustomer, "Successfully", Response::HTTP_OK);
    }


    // public function exportCustomers(Request $request){

    //     $region = DimensionCustomer::where('Region', $request->mregion)->get();
    //     $allCustomer = CustomerResource::collection($region)->response()->getData(true);
    //     return $this->sendSuccess($allCustomer, "Successfully", Response::HTTP_OK);
    // }

    public function getAllDrops(){

        $getDSS = DTWarehouse::select("DSS_11KV_415V_Name", "Assetid", "DSS_11KV_415V_Owner", "hub_name")->get();
        $serviceUnit = ServiceUnit::all();
        $serviceBand = ["E4H", "D8H", "C12H", "B16H", "A20H", "A18H"];
       // $feeders = (new AssetService)->allFeeder();

        $data = [
            'dss' => $getDSS,
            'service_unit' => $serviceUnit,
            'service_band' => $serviceBand,
           // 'feeder' => $feeders
        ];


        return $this->sendSuccess($data, "loaded Successfully", Response::HTTP_OK);

    }



    public function DTBusinessHub() {

        //count(Assetid), hub_name,
        $getDSS = DTBusinessHubResource::collection(DTWarehouse::select("hub_name", DB::raw("COUNT(Assetid) as asset_count"))->groupBy('hub_name')->get());

        return $this->sendSuccess($getDSS, "loaded Successfully", Response::HTTP_OK);

    }


    public function ApprovedCustomers(){

        $baseUrl = env('CUSTOMER_API_URL');
        $getCustomerApproval = $baseUrl . 'my_approvals/'. Auth::user()->id;

        return $response = Http::get($getCustomerApproval);        
       
    }

    

    public function AssignUserMenu(Request $request){

        $getRowID = Role::where('name', $request->role)->first();
        $subMenu = $request->submenu_id;

        //$new_array = [];
       // $getAccess = SubMenu::select("menu_id")->whereIn("id", $request->submenu_id)->get();
         $new_array = SubMenu::whereIn("id", $subMenu)->pluck('menu_id')->toArray();
         $new_array = array_values(array_unique($new_array));
       /* if($getAccess){
          foreach($getAccess as $get){
            array_push($new_array, $get->menu_id);
          }
        }

        return  $new_array;
        */

        // To be deleted later
      /*   return  
        [
          'menu_id' =>  $new_array,
           'sub_id' => $request->submenu_id

        ];
        */

        $menuIds = implode(',', $request->menu_id);

        $updateMenuRole = MenuRole::updateOrCreate(
            ['role_id' => $getRowID->id],    
            [
                'menu_id' =>  "[$menuIds]",

            ]
        );

        if($updateMenuRole){
            return $this->sendSuccess($updateMenuRole, "Record Successfully Updated", Response::HTTP_OK);
        }else {
            return $this->sendError("Error", "No Result Found", Response::HTTP_BAD_REQUEST);
        }
       

    }


    public function userLogout(Request $request){

        $userId = $request->userId;
       // $user = auth()->user()->tokens()->delete();
       
       // $user->tokens()->delete();
       if(!Auth::check()) {
        return $this->sendError("No Data", "Error Loading User Data", Response::HTTP_UNAUTHORIZED);
        }

        $user =  auth()->user()->tokens()->delete();
    
        
        return response()->json(['message' => 'Logged out successfully', 'user' => $user]);
    }



    public function BulkCAADUpload(Request $request){
       
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);
       
          //Handle file upload
         if ($request->has('file')) {

            $file = $request->file('file');

                $timestamp = now()->timestamp; // Generate the current Unix timestamp
                $date = now()->format('Ymd'); // Format the current date as YYYYMMDD  
                // Combine the timestamp, date, and extension to form the unique filename
                $uniqueFileName = $date . '_' . $timestamp;
 
                $fileName = $uniqueFileName.''. uniqid() . '_' . $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();

                $file->storeAs('customercaad', $fileName, 'public');

                $bulkCAAD = BulkCAAD::create([
                    'batch_name' => $request->batch_name,
                    'business_hub' => $request->business_hub,
                    'bulk_unique_id' => uniqid() . '_'. $date.''.$timestamp,
                    'batch_file_name' => $fileName,
                    'region' => $request->region
                ]);
        
                //$batch_id = $bulkCAAD->id; // Get the batch_id from the newly created BulkCAAD model
                
        }

      

        $result =  Excel::import(new CAADImport($bulkCAAD), $file);

        return $this->sendSuccess($result, "Record Successfully Updated", Response::HTTP_OK);
         
        
    }


        public function getAllCAAD(){
            // Assuming you have already retrieved the user role
            $userRole = Auth::user()->roles->pluck('name')->first();
            $getUserRoleObject = (new GeneralService)->getUserLevelRole();

            $getSingleCAAD = ProcessCAAD::with('fileUpload')->with('CaadComment')
                ->where('batch_type', 'single')
                ->when($userRole === 'district_accountant', function ($query) use ($getUserRoleObject) {
                    return $query->where('status', CaadEnum::PENDING->value)->where("business_hub", $getUserRoleObject['business_hub']);
                })
                ->when($userRole === 'businesshub_manager', function ($query) use ($getUserRoleObject) {
                    return $query->where('status', CaadEnum::APPROVED_BY_DISTRICT_ACCOUNTANT->value)->where("business_hub", $getUserRoleObject['business_hub']);
                })
                ->when($userRole === 'audit', function ($query) use ($getUserRoleObject) {
                    return $query->where('status', CaadEnum::APPROVED_BY_BUSINESS_HUB_MANAGER->value)->where("business_hub", $getUserRoleObject['business_hub']);
                })
                ->when($userRole === 'regional_head', function ($query) use ($getUserRoleObject) {
                    return $query->where('status', CaadEnum::APPROVED_BY_AUDIT->value)->where("region", $getUserRoleObject['region']);
                })
                ->when($userRole === 'hcs', function ($query) {
                    return $query->where('status', CaadEnum::APPROVED_BY_REGIONAL_MANAGER->value);
                })
                ->when($userRole === 'cco', function ($query) {
                    return $query->where('status', CaadEnum::APPROVED_BY_HCS->value);
                })
                ->when($userRole === 'md', function ($query) {
                    return $query->where('status',  CaadEnum::APPROVED_BY_CCO->value);
                }) 
                ->when($userRole === 'admin', function ($query) {
                    return $query->whereIn('status', [0, 1, 2, 3, 4, 5, 6, 7, 10]);
                })
                ->orderBy('id', 'desc')
            ->paginate(20);


            $getBatchCAAD = BulkCAAD::with('withmanycaads')->withCount('withmanycaads')->with('withmayncomments')->withCount('withmayncomments')
                ->when($userRole === 'district_accountant', function ($query) use ($getUserRoleObject)  {
                    return $query->where('batch_status', CaadEnum::PENDING->value)->where("business_hub", $getUserRoleObject['business_hub']);
                })
                ->when($userRole === 'businesshub_manager', function ($query) use ($getUserRoleObject) {
                    return $query->where('batch_status', CaadEnum::APPROVED_BY_DISTRICT_ACCOUNTANT->value)->where("business_hub", $getUserRoleObject['business_hub']);
                })
                ->when($userRole === 'audit', function ($query) use ($getUserRoleObject) {
                    return $query->where('batch_status', CaadEnum::APPROVED_BY_BUSINESS_HUB_MANAGER->value)->where("business_hub", $getUserRoleObject['business_hub']);
                })
                ->when($userRole === 'regional_head', function ($query) use ($getUserRoleObject) {
                    return $query->where('batch_status', CaadEnum::APPROVED_BY_AUDIT->value)->where("region", $getUserRoleObject['region']);
                })
                ->when($userRole === 'hcs', function ($query) {
                    return $query->where('batch_status', CaadEnum::APPROVED_BY_REGIONAL_MANAGER->value);
                })
                ->when($userRole === 'cco', function ($query) {
                    return $query->where('batch_status', CaadEnum::APPROVED_BY_HCS->value);
                })
                ->when($userRole === 'md', function ($query) {
                    return $query->where('batch_status',  CaadEnum::APPROVED_BY_CCO->value);
                }) 
                ->when($userRole === 'admin', function ($query) {
                    return $query->whereIn('batch_status', [0, 1, 2, 3, 4, 5, 6, 7, 10]);
                })
            ->orderBy('id', 'desc')->paginate(20);

            $data = [
                'single' => $getSingleCAAD,
                'batch' => $getBatchCAAD
            ];

            return $this->sendSuccess($data, "Record Successfully Updated", Response::HTTP_OK);

        }




        public function addCAAD(CaadRequest $request){ //
           

        try {


            if(isset($request->update_id)){
                $getwhocreated = ProcessCAAD::find($request->update_id);
                // if($getwhocreated->created_by  != $request->update_id){
                //     return $this->sendError("Error", "You are not authorized to update this request", Response::HTTP_BAD_REQUEST);
                // }
                
                //update CAAD information 
                $processCAAD = ProcessCAAD::where("id", $request->update_id)->update([
                    'accountNo' => $request->accountNo,
                    'phoneNo' => $request->phoneNo,
                    'surname' => $request->surname,
                    'lastname' => $request->lastname,
                    'othername' => $request->othername,
                    'service_center' => $request->service_center,
                    'meterno' => $request->meterno,
                    'accountType' => $request->accountType,
                    'transtype' => $request->transtype,
                    'meter_reading' => $request->meter_reading,
                    'transaction_type' => $request->transaction_type,
                    'effective_date' => $request->effective_date,
                    'amount' => isset($request->amount) ? $request->amount : $getwhocreated->amount,
                    'remarks' => $request->remarks,
                    'file_upload_id' => 0,
                    'business_hub' => $request->business_hub,
                    'region' => $request->region,
                    'created_by' => Auth::user()->id,
                ]);

            }else {


                
                $validator = Validator::make($request->file('file_upload'), [
                    'file_upload' => 'sometimes|nullable|array', // Ensure it's an array of files
                    'file_upload.*' => 'sometimes|nullable|mimes:jpeg,jpg,png,pdf,csv,xlsx|max:2048', // Add allowed file types here
                    // other validation rules for other form fields if required
                ]);
        
                $validator->after(function ($validator) use ($request) {
                    $files = $request->file('file_upload');
            
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            if ($file !== null && !$file->isValid()) {
                                $validator->errors()->add('file_upload', 'Invalid file upload.');
                                break; // Stop processing if any file is invalid
                            }
                        }
                    }
                });
            
                if ($validator->fails()) {
                    return $this->sendError("Validation Error", $validator->errors(), Response::HTTP_BAD_REQUEST);
                }


                $processCAAD = ProcessCAAD::create([
                    'accountNo' => $request->accountNo,
                    'phoneNo' => $request->phoneNo,
                    'surname' => $request->surname,
                    'lastname' => $request->lastname,
                    'othername' => $request->othername,
                    'service_center' => $request->service_center,
                    'meterno' => $request->meterno,
                    'accountType' => $request->accountType,
                    'transtype' => $request->transtype,
                    'meter_reading' => $request->meter_reading,
                    'transaction_type' => $request->transaction_type,
                    'effective_date' => $request->effective_date,
                    'amount' => $request->amount,
                    'remarks' => $request->remarks,
                    'file_upload_id' => 0,
                    'business_hub' => $request->business_hub,
                    'region' => $request->region,
                    'created_by' => Auth::user()->id,
    
                ]);

            } 

           
              // Check if the destination folder exists and has write permissions is_writable
              $destinationPath = public_path('customercaad/');
              if (!file_exists($destinationPath)) {
                  return $this->sendError("Error", "Destination folder 'customercaad' is does not exist.", Response::HTTP_INTERNAL_SERVER_ERROR);
              }else if(!is_writable($destinationPath)){
                  return $this->sendError("Error", "Destination folder 'customercaad' is not writable.", Response::HTTP_INTERNAL_SERVER_ERROR);
              }

             
             //Handle file upload
            if ($request->has('file_upload')) {

                $files = $request->file('file_upload');
               
                foreach ($files as $file) {

                    $timestamp = now()->timestamp; // Generate the current Unix timestamp
                    $date = now()->format('Ymd'); // Format the current date as YYYYMMDD  
                    // Combine the timestamp, date, and extension to form the unique filename
                    $uniqueFileName = $date . '_' . $timestamp;

                    $fileName = $uniqueFileName.''. uniqid() . '_' . $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();

                    $file->storeAs('customercaad', $fileName, 'public');

                    $uploadfile = FileCAAD::create([
                        'process_caad_id' => isset($request->update_id) ? $request->update_id : $processCAAD->id,
                        'file_name' => $fileName,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getClientMimeType(),
                        'file_link' => 'customercaad/',
                    ]);
                }
    
            }

            $getUpdate = isset($request->update_id) ? $getwhocreated->where("id", $request->update_id)->first() : $processCAAD;
         

          return $this->sendSuccess($getUpdate, "File Successfully Uploaded", Response::HTTP_CREATED);

        }catch(\Exception $e){

            return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        


            
    
        }




        public function CaadApprovalRequest(Request $request){
          //  return CaadEnum::ADMIN->value;
          // return CaadEnum::APPROVED_BY_MD->label();

            try{
                // Get the user role
                $userRole = Auth::user()->roles->pluck('name')->first();
                DB::beginTransaction();
                // Check if the batch type is single
                    if ($request->batch_type == 'single') {
                        // Update the process CAAD status
                        $processCAAD = ProcessCAAD::find($request->id);
                        $processCAAD->status = $this->getApprovalStatus($userRole);
                        $processCAAD->save();
                    }else {
                        
                        $processBatch = BulkCAAD::find($request->id);
                        $processBatch->batch_status = $this->getApprovalStatus($userRole);
                        $processBatch->save();

                        //Now Update the processCADD where batch id is = batch
                        $processCARD = ProcessCAAD::where('batch_id', $request->id)->update([
                            'status' => $this->getApprovalStatus($userRole)
                        ]);
                    }

                    // Add a comment
                   $secret =  $this->addComment($request, $userRole);
                    DB::commit();
                    return $this->sendSuccess($secret, "CAAD Successfully Approved", Response::HTTP_CREATED);

            } catch(\Exception $e){
                DB::rollBack();
                return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
           

        }


        public function CaadRejectRequest(Request $request){
  
              try{
                  // Get the user role
                  $userRole = Auth::user()->roles->pluck('name')->first();
                  DB::beginTransaction();
                  // Check if the batch type is single
                      if ($request->batch_type == 'single') {
                          // Update the process CAAD status
                          $processCAAD = ProcessCAAD::find($request->id);
                          $processCAAD->status = 10;
                          $processCAAD->save();
                      }else {

                        $processBatch = BulkCAAD::find($request->id);
                        $processBatch->batch_status = 10;
                        $processBatch->save();

                        //Now Update the processCADD where batch id is = batch
                        $processCARD = ProcessCAAD::where('batch_id', $request->id)->update([
                            'status' => 10
                        ]);

                      }
  
                      // Add a comment
                      $this->addRejectComment($request, $userRole);
                      DB::commit();
                      return $this->sendSuccess($processCAAD, "CAAD Successfully Approved", Response::HTTP_CREATED);
  
              } catch(\Exception $e){
                  DB::rollBack();
                  return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
              }
             
  
        }



        private function getApprovalStatus(string $userRole)
        {
            switch ($userRole) {
                case 'district_accountant':
                    return CaadEnum::APPROVED_BY_DISTRICT_ACCOUNTANT->value;
                case 'businesshub_manager':
                    return CaadEnum::APPROVED_BY_BUSINESS_HUB_MANAGER->value;
                case 'audit':
                    return CaadEnum::APPROVED_BY_AUDIT->value;
                case 'regional_manager':
                    return CaadEnum::APPROVED_BY_REGIONAL_MANAGER->value;
                case 'hcs':
                    return CaadEnum::APPROVED_BY_HCS->value;
                case 'cco':
                    return CaadEnum::APPROVED_BY_CCO->value;
                case 'md':
                    return CaadEnum::APPROVED_BY_MD->value;
                case 'admin':
                    return CaadEnum::ADMIN->value;
                default:
                    throw new \Exception('Invalid user role');
            }
        }

        private function addComment(Request $request, string $userRole)
        {

            // Get the label for the given userRole from the CaadEnum
            $userRoleLabel = match ($userRole) {
                'district_accountant' => CaadEnum::APPROVED_BY_DISTRICT_ACCOUNTANT->label(),
                'businesshub_manager' => CaadEnum::APPROVED_BY_BUSINESS_HUB_MANAGER->label(),
                'audit' => CaadEnum::APPROVED_BY_AUDIT->label(),
                'regional_manager' => CaadEnum::APPROVED_BY_REGIONAL_MANAGER->label(),
                'hcs' => CaadEnum::APPROVED_BY_HCS->label(),
                'cco' => CaadEnum::APPROVED_BY_CCO->label(),
                'md' => CaadEnum::APPROVED_BY_MD->label(),
                'admin' => CaadEnum::ADMIN->label(),
                default => '',
            };

          return  $caadComment = CAADCommentApproval::create([
                'process_caad_id' => $request->id,
                'approval_type' => $request->batch_type,
                'batch_id' => isset($request->batch_id) ? $request->batch_id : 0,
                'approval_by' => Auth::user()->name,
                'comments' =>  $userRoleLabel . ' @ ' . ' ' . Date('Y-m-d H:i:s'),
            ]);
        }


        private function addRejectComment(Request $request, string $userRole)
        {

            $caadComment = CAADCommentApproval::create([
                'process_caad_id' => $request->id,
                'approval_type' => $request->batch_type,
                'batch_id' => isset($request->batch_id) ? $request->batch_id : 0,
                'approval_by' => Auth::user()->name,
                'comments' =>  'Rejected By ' . $userRole. ' @ ' . ' ' . Date('Y-m-d H:i:s'),
            ]);
        }


    


        public function changePassword(Request $request){

            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required|string|min:6',
            ]);
    
            $user = Auth::user();
    
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->sendError("Error", "Your current password does not matches with the password you provided. Please try again.", Response::HTTP_BAD_REQUEST);
            }
    
            if (strcmp($request->old_password, $request->new_password) == 0) {
                return $this->sendError("Error", "New Password cannot be same as your current password. Please choose a different password.", Response::HTTP_BAD_REQUEST);
            }
    
            //Hash::make($request->new_password)
            $user->password = bcrypt($request->new_password);
            $user->save();
    
            return $this->sendSuccess($user, "Password changed successfully !", Response::HTTP_OK);
        }


      /*  public function sendPasswordReset(Request $request){

            $request->validate([
                'email' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendError("Error", "We can't find a user with that e-mail address.", Response::HTTP_BAD_REQUEST);
            }

            $token = Str::random(60);
            $user->password_reset_token = $token;
            $user->save();

            //Send Email as an event
           // event(new PasswordResetEvent($user));


        }
        */

        public function forgotPassword(Request $request){

            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendError("Error", "We can't find a user with that e-mail address.", Response::HTTP_BAD_REQUEST);
            }

            $status = Password::sendResetLink($request->only('email') );

           /* return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
            */

            $neStatus = Password::RESET_LINK_SENT;

            $getToken = DB::table('password_reset_tokens')->where('email', $request->email)->first();

            return $this->sendSuccess($getToken, "Password changed Sent !", Response::HTTP_OK);

        }
    
    
        public function resetPassword(Request $request) {

            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([ 'password' => Hash::make($password) ])->setRememberToken(Str::random(60));
         
                    $user->save();
         
                    event(new PasswordReset($user));
            });

            $status === Password::PASSWORD_RESET;
            
        }


        public function checkPassword($checkpassword){
            if(!$checkpassword){
                return $this->sendError("Error", "We can't find a user with that e-mail address.", Response::HTTP_BAD_REQUEST);
            } 

            $getToken = DB::table('password_reset_tokens')->where('token', $checkpassword)->first();

            if($getToken) {
                return $this->sendSuccess($getToken, "Token Exists", Response::HTTP_OK);
            }else {
                return $this->sendError("Error", "We can't find a user with that e-mail address.", Response::HTTP_BAD_REQUEST);
            }
        }






        public function allCAAD(){
            // Assuming you have already retrieved the user role
            $userid = Auth::user()->id;
            $getUserRoleObject = (new GeneralService)->getUserLevelRole();
            $userRole = Auth::user()->roles->pluck('name')->first();
    
            $getSingleCAAD = ProcessCAAD::with('fileUpload')->with('CaadComment')
                ->where('batch_type', 'single')
                ->when($userRole === 'credit_control', function ($query)  use ($userid, $getUserRoleObject){
                    return $query->where('created_by', $userid)->where("region", $getUserRoleObject['region'])->orderBy("created_at", "desc");
                })
                ->when($userRole === 'district_accountant', function ($query) use ($userid, $getUserRoleObject) {
                    return $query->where('district_accountant', $userid)->where("business_hub", $getUserRoleObject['business_hub'])->orderBy("created_at", "desc");
                })
                // ->when($userRole === 'businesshub_manager', function ($query) use ($userid, $getUserRoleObject) {
                //     return $query->where('business_hub_manager', $userid)->where("business_hub", $getUserRoleObject['business_hub'])->orderBy("created_at", "desc");
                // })
                // ->when($userRole === 'audit', function ($query) use ($userid, $getUserRoleObject) {
                //     return $query->where('audit', $userid)->where("business_hub", $getUserRoleObject['business_hub'])->orderBy("created_at", "desc");
                // })
                // ->when($userRole === 'regional_manager', function ($query) use ($userid, $getUserRoleObject) {
                //     return $query->where('regional_manager', $userid)->where("region", $getUserRoleObject['region'])->orderBy("created_at", "desc");
                // })
                // ->when($userRole === 'hcs', function ($query) {
                //     return $query->whereIn('accountType', ['Prepaid', 'Postpaid'])->orderBy("created_at", "desc");
                // })
                // ->when($userRole === 'cco', function ($query) {
                //     return $query->whereIn('accountType', ['Prepaid', 'Postpaid'])->orderBy("created_at", "desc");
                // })
                // ->when($userRole === 'md', function ($query) {
                //     return $query->where('accountType',  ['Prepaid', 'Postpaid'])->orderBy("created_at", "desc");
                // }) 
                // ->when($userRole === 'admin', function ($query) {
                //     return $query->whereIn('status', [0, 1, 2, 3, 4, 5, 6, 7, 10])->orderBy("created_at", "desc");
                // }, function ($query) {
                //     // Default logic for roles that do not match any of the previous conditions
                //     return $query->whereIn('accountType', ['Prepaid', 'Postpaid'])->orderBy("created_at", "desc");
                // })
                ->orderBy('id', 'desc')
                ->paginate(30);
    
    
                $getBatchCAAD = BulkCAAD::with('withmanycaads')->withCount('withmanycaads')
                    ->with('withmayncomments')->withCount('withmayncomments')
                    ->when(in_array($userRole, ['district_accountant', 'businesshub_manager', 'audit']), function ($query) use ($userRole, $userid, $getUserRoleObject) {
                        $query->where('business_hub', $getUserRoleObject['business_hub']);
    
                        if ($userRole === 'district_accountant') {
                            $query->where('district_accountant', $userid);
                        } elseif ($userRole === 'businesshub_manager') {
                            $query->where('business_hub_manager', $userid);
                        } elseif ($userRole === 'audit') {
                            $query->where('batch_status', $userid);
                        }
    
                        return $query;
                    })
                    ->when($userRole === 'regional_manager', function ($query) use ($userid, $getUserRoleObject) {
                        return $query->where('regional_manager', $userid)->where('region', $getUserRoleObject['region']);
                    })
                    ->when(in_array($userRole, ['hcs', 'cco', 'md', 'admin']), function ($query) {
                        return $query->orderBy('created_at', 'desc');
                    })
                    ->orderBy('id', 'desc')
                ->paginate(10);
    
    
    
            $data = [
                'single' => $getSingleCAAD,
                'batch' => $getBatchCAAD
            ];
    
            return $this->sendSuccess($data, "Record Successfully Updated", Response::HTTP_OK);
    
        }
    
    


   



}
