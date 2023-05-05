<?php

namespace App\Repositories;

use App\Repositories\AssetRepositoryInterface;
use App\Models\FeederEleven;
use App\Models\FeederThirty;
use App\Enums\FeederEnum;

class AssetRepository implements AssetRepositoryInterface
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
