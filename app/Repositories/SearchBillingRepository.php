<?php

namespace App\Repositories;

use App\Repositories\SearchRepositoryInterface;
use App\Models\ZoneBills;



class SearchBillingRepository implements SearchRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search(){

       $search_term =  $this->request->Bill;

        $customers =  ZoneBills::select('*')->where(function ($query) use ($search_term ) {
            $query->where('AccountNo', 'LIKE', $search_term . '%');
            $query->orWhere('CustomerName', $search_term );
           // $query->orWhere('BillID', $search_term);
            $query->orWhere('BUName1', $search_term);
        })->orderBy('Billdate', 'desc')->paginate(60);  //first();
       // Execute search implementation here
        return $customers;
    }

   

}
