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

class TestController extends BaseApiController
{
    public function login(LoginRequest $request): Object
    {

        if($request->expectsJson()) {

            $userStatus = User::where('email', $request->email)->value('status');

            if($userStatus == 0 || $userStatus == 'NULL'){
                return $this->sendSuccess('Invalid Status', "No Activation Included in the account", Response::HTTP_UNAUTHORIZED);
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
       // $TotalTickets = Tickets::count();
    
        $data = [
            'total_dss' => $TotalDSS,
            'total_customers' => $TotalCustomers, //DB::connection('stagging')->table("ems_customers")->count(),
            'feeder_11' => $TotalFeederEl, //DB::connection('stagging')->table("gis_11KV Feeder")->count(),
            'feeder_33' => $TotalFeederThirty, //DB::connection('stagging')->table("gis_33KV Feeder")->count(),
           //'crm_tickets' => $TotalTickets  //DB::connection('crm')->table("tickets")->count(), // Access denied issue to be fixed by infrastructure  //$TotalTickets
        ];

        return $this->sendSuccess($data, "Asset Information Saved Successfully", Response::HTTP_OK);

    }

    public function getUser(){
        $user = Auth::user();
        return $this->sendSuccess($user, "User Information", Response::HTTP_OK);
    }


}
