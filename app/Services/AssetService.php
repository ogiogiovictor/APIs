<?php

namespace App\Services;
use App\Models\DimensionCustomer;
use App\Models\ZoneCustomer;
use Illuminate\Support\Facades\DB;
use App\Models\DTWarehouse;
use App\Models\FeederEleven;
use App\Models\FeederThirty;
use App\Models\Tickets;
use App\Enums\FeederEnum;
use App\Models\ServiceUnit;


class AssetService
{
    
  
  
    public function getFeederEleven() {

     
        $eleven =  FeederEleven::orderby("Capture DateTime", "desc")->paginate(20);

        return $eleven;
    
       }
    
       public function getFeederThirty() {
    
       
        $thirty =  FeederThirty::orderby("Capture DateTime", "desc")->paginate(20);
        

       

        return $thirty;
    
       }

       public function allFeeder() {

        $eleven = FeederEleven::orderby("Capture DateTime", "desc")->get(); 
        $thirty = Feederthirty::orderby("Capture DateTime", "desc")->get();
        $merged = $eleven->merge($thirty)->rowpageme(10);

        return $merged;

       }


}
