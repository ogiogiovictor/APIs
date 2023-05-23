<?php

namespace App\Repositories;

use App\Repositories\SearchRepositoryInterface;
use App\Models\DTWarehouse;



class SearchAssetRepository implements SearchRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search(){

        $search_term =  $this->request->DT;
        $customers =  DTWarehouse::select('*')->where(function ($query) use ($search_term ) {
            $query->where('DSS_11KV_415V_Name', 'like', '%'. $search_term .  '%');
            $query->orWhere('Assetid', $search_term );
            $query->orWhere('DSS_11KV_415V_Address', $search_term);
        })->get(); 

        return $customers;
    }

   

}
