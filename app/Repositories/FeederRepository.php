<?php

namespace App\Repositories;

use App\Repositories\FeederRepositoryInterface;
use App\Models\FeederEleven;
use App\Models\FeederThirty;
use App\Enums\FeederEnum;

class FeederRepository implements FeederRepositoryInterface
{
   

   public function storeDTs($data, $assetType){
    
        if($assetType == FeederEnum::FT_eleven()->value){
            return FeederEleven::create($data);
        }else if($assetType == FeederEnum::FT_thirty_three()->value){
            return FeederThirty::create($data);
        }else{
            return "Bad Asset Type";
        }
   }
  



}
