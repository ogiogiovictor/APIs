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
            'total_dss' => $TotalDSS,
            'total_customers' => $TotalCustomers, //DB::connection('stagging')->table("ems_customers")->count(),
            'feeder_11' => $TotalFeederEl, //DB::connection('stagging')->table("gis_11KV Feeder")->count(),
            'feeder_33' => $TotalFeederThirty, //DB::connection('stagging')->table("gis_33KV Feeder")->count(),
           'crm_tickets' => $TotalTickets,  //DB::connection('crm')->table("tickets")->count(), // Access denied issue to be fixed by infrastructure  //$TotalTickets
           'customer_by_region' => $CustomerByRegion,
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

        if($request->type == 'postpaid'){

            $customers = DimensionCustomer::whereIn('StatusCode', ['A', 'S'])->where("AccountType", $request->type)->paginate(20); //getPostpaid

            return $this->sendSuccess($customers, "Customer Successfully Loaded", Response::HTTP_OK);

        } else if($request->type == 'prepaid'){

            $customers = DimensionCustomer::whereIn('StatusCode', ['0', '1'])->where("AccountType", $request->type)->paginate(20); //getPrepaid

            return $this->sendSuccess($customers, "Customer Successfully Loaded", Response::HTTP_OK);

        }else {

            $customers = DimensionCustomer::select('SetupDate', 'AccountNo', 'BookNo', 'MeterNo', 'Mobile', 'OldAccountNo', 'TariffID', 'Surname', 'FirstName', 'OtherNames', 'AcctTypeDesc',
            'OldTariffCode', 'TarriffCode', 'AccountType', 'Address', 'BUID', 'BusinessHub', 'service_center', 'UTID',
            'ConnectionType', 'ArrearsBalance', 'State', 'City', 'StatusCode')->whereIn("StatusCode", ['A', 'S', '1', '0'])->paginate(15); //getAll

            return $this->sendSuccess($customers, "Customer Successfully Loaded", Response::HTTP_OK);

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

            return $this->sendSuccess($customer, "Customer 360 Loaded", Response::HTTP_OK);

        }catch(\Exception $e) {
            return $this->sendError("No Data", $e , Response::HTTP_UNAUTHORIZED);
        }

    }


}
