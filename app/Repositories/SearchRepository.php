<?php

namespace App\Repositories;

use App\Repositories\SearchRepositoryInterface;
use App\Models\DimensionCustomer;
use App\Models\DTWarehouse;
use App\Http\Resources\CustomerResource;



class SearchRepository implements SearchRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search(){

       $search_term =  $this->request->AccountNo;

        $customers = DimensionCustomer::select('*')->where(function ($query) use ($search_term ) {
            //$query->whereNotIn("StatusCode", ["0, I, C, N"]);
           // $query->where('Surname', $search_term);
            $query->where('AccountNo', 'like', '%'. $search_term .  '%');
            $query->orWhere('MeterNo', $search_term );
            $query->orWhere('Surname', $search_term);
        })->get();  //first();
       // Execute search implementation here
       // return  $customers;
        return  CustomerResource::collection($customers);
    }

   

}
