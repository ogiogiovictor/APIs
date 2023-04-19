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

        $postpaid = DimensionCustomer::selectRaw('StatusCode, count(*) as total')
        ->where("AccountType", 'Postpaid')->groupBy('StatusCode')->get();

        $prepaid = DimensionCustomer::selectRaw('StatusCode, count(*) as total')
        ->where("AccountType", 'Prepaid')->groupBy('StatusCode')->get();


        if($request->type == 'Postpaid'){

           $customers = DimensionCustomer::whereIn('StatusCode', ['A', 'S'])->where("AccountType", $request->type)->paginate(20); //getPostpaid

           $data = [
            'customers' => $customers,
            'postpaid' => $postpaid,
            'prepaid' => $prepaid,
           ];

            return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

        } else if($request->type == 'prepaid'){

            $customers = DimensionCustomer::whereIn('StatusCode', ['0', '1'])->where("AccountType", $request->type)->paginate(20); //getPrepaid

            $data = [
                'customers' => $customers,
                'postpaid' => $postpaid,
                'prepaid' => $prepaid,
               ];

            return $this->sendSuccess($customers, "Customer Successfully Loaded", Response::HTTP_OK);

        }else {

            $customers = DimensionCustomer::select('SetupDate', 'AccountNo', 'BookNo', 'MeterNo', 'Mobile', 'OldAccountNo', 'TariffID', 'Surname', 'FirstName', 'OtherNames', 'AcctTypeDesc',
            'OldTariffCode', 'TarriffCode', 'AccountType', 'Address', 'BUID', 'BusinessHub', 'service_center', 'UTID',
            'ConnectionType', 'ArrearsBalance', 'State', 'City', 'StatusCode')->whereIn("StatusCode", ['A', 'S', '1', '0'])->paginate(15); //getAll

            $data = [
                'customers' => $customers,
                'postpaid' => $postpaid,
                'prepaid' => $prepaid,
               ];

            return $this->sendSuccess($data, "Customer Successfully Loaded", Response::HTTP_OK);

        }

    }


    public function getAssetWH(Request $request){

        if($request->type == AssetEnum::DT_eleven()->value){
            return DTWarehouse::where("assettype", AssetEnum::DT_eleven()->value)->paginate(20);
        } else if($request->type == AssetEnum::DT_thirty_three()->value) {
            returnDTWarehouse::where("assettype", AssetEnum::DT_thirty_three()->value)->paginate(20);
        }else {
            return DTWarehouse::paginate(20)->toArray();
        }

    }



    public function findex(Request $request){

        if($request->type == FeederEnum::FT_eleven()->value){  //11KV Feeder  11KV Feeder
            
            $feeder = FeederEleven::where("assettype", FeederEnum::FT_eleven()->value)->paginate(20); 
            return $feeder;

         }else if($request->type == FeederEnum::FT_thirty_three()->value){
        
            $feeder = Feederthirty::where("assettype", FeederEnum::FT_thirty_three()->value)->paginate(20); 
            return $feeder;
        
        }else {

            $eleven = FeederEleven::get(); 
            $thirty = Feederthirty::get();
            $feeders = $eleven->merge($thirty)->rowpageme(10);
    
            return $feeders;
        }

    }


    public function tindex() {
        $tickets = Tickets::paginate(20);
        return $tickets;
    }


    
    public function tshow(Request $request){

        // $validator = Validator::make($request, [
        //     'ticketid' => 'required',
        // ]);

        // if($validator->fails()){
        //     return response()->json([ 'error' => true, 'message' => $validator->errors() ]);
        // }

        $ticket = Tickets::where('ticket_no', $request->ticketid)->first();
        //Get the Customer Details in CRM
        $getAccountNo = CRMUsers::where('id', $ticket->user_id)->first();
       // Now Get the Customer Information.
        $customer = DimensionCustomer::where('AccountNo', $getAccountNo->accountno)
        ->orWhere('MeterNo', $getAccountNo->accountno)->first();

        $data = [
            'ticket' => $ticket,
            'customer' => $customer,
        ];

        if($ticket){
            return $this->sendSuccess($data, "Customer 360 Loaded", Response::HTTP_OK);
        }else {
            return $this->sendError("No Data", "No data Found" , Response::HTTP_NO_CONTENT);
        } 

    }


    public function customer360($acctionNo, $dss){

        try {

           $changeAccountNumber = StringHelper::formatAccountNumber($acctionNo);

            $customer = DimensionCustomer::with('bills')->where('AccountNo', $changeAccountNumber)->first();

            if ($customer->AccountType == 'Postpaid') {
                $customer->load('payments');
            } elseif ($customer->AccountType == 'Prepaid') {
                $customer->load('transactions');
            }
           

            $distribution = DTWarehouse::select('Assetid', 'assettype', 'AssetName', 'DSS_11KV_415V_Make',
             'DSS_11KV_415V_Rating', 'DSS_11KV_415V_Address', 'DSS_11KV_415V_Owner', 
             'DSS_11KV_415V_parent', 'longtitude', 'latitude', 'naccode')->where('Assetid', $dss)->first();
            $customer->distribution = $distribution;

            $crm_user = CRMUsers::where('accountno', $changeAccountNumber)->first();
            //Get the Tickets
             $tickets = Tickets::where('user_id', $crm_user->id)->get();

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


            $customer->distribution = $distribution;
            $customer->tickets = $tickets;
            $customer->msmsCustomerInfo = $msmsMeters;


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
            
            return $this->sendSuccess($data, "Bills Loaded", Response::HTTP_OK);
        }catch(\Exception $e) {
            return $this->sendError("No Bills", $e , Response::HTTP_UNAUTHORIZED);
        }
    }




}
