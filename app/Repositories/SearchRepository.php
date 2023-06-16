<?php

namespace App\Repositories;

use App\Repositories\SearchRepositoryInterface;
use App\Models\DimensionCustomer;
use App\Models\DTWarehouse;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\Auth;
use App\Services\GeneralService;




class SearchRepository implements SearchRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search()
    {
        $search_term = $this->request->AccountNo;
    
        $user = Auth::user();
        $getSpecialRole = (new GeneralService)->getSpecialRole();
    
        // Define and retrieve $getUserRoleObject within the current scope
        $getUserRoleObject = (new GeneralService)->getUserLevelRole();

        //return $getUserRoleObject['business_hub'];
    
        if (in_array($getUserRoleObject['role'], $getSpecialRole) && $user->isHQ()) {
            $customers = DimensionCustomer::select('*')->where(function ($query) use ($search_term) {
                // $query->whereNotIn("StatusCode", ["0, I, C, N"]);
                // $query->where('Surname', $search_term);
                $query->where('AccountNo', 'like', '%' . $search_term . '%');
                $query->orWhere('MeterNo', $search_term);
                $query->orWhere('Surname', $search_term);
            })->get();
        } else if ($user->isRegion()) {
            $customers = DimensionCustomer::select('*')->where(function ($query) use ($search_term, $getUserRoleObject) {
                $query->where('AccountNo', 'like', '%' . $search_term . '%');
                $query->where("Region", $getUserRoleObject['region']);
                $query->orWhere('MeterNo', $search_term);
                $query->orWhere('Surname', $search_term);
            })->get();
        } else if ($user->isBhub()) {
           $customers = DimensionCustomer::select('*')->where(function ($query) use ($search_term, $getUserRoleObject) {
                $query->where('AccountNo', 'like', '%' . $search_term . '%');
                $query->orWhere('MeterNo', $search_term);
                $query->orWhere('Surname', $search_term);
                $query->where("Region", $getUserRoleObject['region']);
                $query->where("BusinessHub", $getUserRoleObject['business_hub']);
               
            })->get();
        } else if ($user->isSCenter()) {
            $customers = DimensionCustomer::select('*')->where(function ($query) use ($search_term, $getUserRoleObject) {
                $query->where('AccountNo', 'like', '%' . $search_term . '%');
                $query->where("Region", $getUserRoleObject['region']);
                $query->where("BusinessHub", $getUserRoleObject['business_hub']);
                $query->orWhere("BUID", $getUserRoleObject['business_hub']);
                $query->where("service_center", $getUserRoleObject['sc']);
                $query->orWhere('MeterNo', $search_term);
                $query->orWhere('Surname', $search_term);
            })->get();
        }
    
        return CustomerResource::collection($customers);
    }
    

   

}
