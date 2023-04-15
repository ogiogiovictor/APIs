<?php

namespace App\Services;
use App\Models\DimensionCustomer;
use App\Models\ZoneCustomer;
use DB;
use App\Models\DTWarehouse;
use App\Models\FeederEleven;
use App\Models\FeederThirty;
use App\Models\Tickets;
use App\Enums\FeederEnum;


class AssetService
{
    

  
    public function getFeederEleven() {

        $getAsset = FeederEleven::where("assettype", FeederEnum::FT_eleven()->value)->paginate(20);
        return $getAsset;
    
       }
    
       public function getFeederThirty() {
    
        $getAsset = Feederthirty::where("assettype", FeederEnum::FT_thirty_three()->value)->paginate(20);
        return $getAsset;
    
       }

       public function allFeeder() {
     
        //collect(FeederEleven::get());

        $eleven = FeederEleven::get(); 
        $thirty = Feederthirty::get();
        $merged = $eleven->merge($thirty)->rowpageme(10);

        return $merged;

       }


}
