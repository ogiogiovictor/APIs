<?php

namespace App\Repositories;

use App\Repositories\SearchRepositoryInterface;
use App\Models\DimensionCustomer;
use App\Models\FeederEleven;
use App\Models\FeederThirty;





class SearchFeederRepository implements SearchRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search(){

       $search_term =  $this->request->Feeder;

       $customers = null;

        $feederElevenCustomers = FeederEleven::select('*')
            ->where('Assetid', $search_term)
            ->orWhere('F11kvFeeder_Name', $search_term)
            ->paginate(50);

        if ($feederElevenCustomers->count() > 0) {
            $customers = $feederElevenCustomers;
        } else {
            $feederThirtyCustomers = FeederThirty::select('*')
                ->where('Assetid', $search_term)
                ->orWhere('F33kv_Feeder_Name', $search_term)
                ->paginate(50);

            if ($feederThirtyCustomers->count() > 0) {
                $customers = $feederThirtyCustomers;
            }
        }


        return $customers;
    }

   

}
