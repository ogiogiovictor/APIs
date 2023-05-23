<?php

namespace App\Repositories;

use App\Repositories\AssetRepositoryInterface;
use App\Models\DTEleven;
use App\Models\DTThirty;
use App\Enums\AssetEnum;
use App\Enums\FeederEnum;
use App\Models\ServiceUnit;
use App\Models\DTWarehouse;
use App\Models\FeederEleven;
use App\Models\FeederThirty;

class AssetRepository implements AssetRepositoryInterface
{
   public function allDTs() {

       // $dtArrary = [];
        $getEleven = DTEleven::paginate(20)->toArray();
        $getThirty = DTThirty::paginate(20)->toArray();
        return [
            'eleven' => $getEleven,
            'thirtythree' => $getThirty
        ];
   }

   public function storeDTs($data, $assetType){
    
        if($assetType == AssetEnum::DT_eleven()->value){
            return DTEleven::create($data);
        }else if($assetType == AssetEnum::DT_thirty_three()->value){
            return DTThirty::create($data);
        }else{
            return "Bad Asset Type";
        }
   }



   public function allServiceUnit(){
    
        $serviceUnit = ServiceUnit::paginate(50)->toArray();
        return [
            'service_units' =>  $serviceUnit,
        ];
   }


   //Get Asset From Warehouse
   public function getAllDSSW() {
    $elevenDt = DTWarehouse::where('assettype', AssetEnum::DT_eleven()->value)->count();
    $thirtyDt = DTWarehouse::where('assettype', AssetEnum::DT_thirty_three()->value)->count();
    $dtTotal = DTWarehouse::count();

    $getDTs = DTWarehouse::withCount('getCustomerCount')->paginate(20);

    $data =[
                'allDt' => $getDTs,
                'elevenDt' => $elevenDt,
                'thirtyDt' => $thirtyDt,
                'dtTotal' => $dtTotal,
    ];

    return $data;
   }


   public function getAllEleven() {

    $elevenDt = DTWarehouse::where('assettype', AssetEnum::DT_eleven()->value)->count();
    $thirtyDt = DTWarehouse::where('assettype', AssetEnum::DT_thirty_three()->value)->count();
    $dtTotal = DTWarehouse::count();

    $getDTs = DTWarehouse::withCount('getCustomerCount')
    ->where('assettype', AssetEnum::DT_eleven()->value)
    ->paginate(20);

    $data =[
        'allDt' => $getDTs,
        'elevenDt' => $elevenDt,
        'thirtyDt' => $thirtyDt,
        'dtTotal' => $dtTotal,
    ];

    return $data;
   }

   public function getAllThirty() {

    $elevenDt = DTWarehouse::where('assettype', AssetEnum::DT_eleven()->value)->count();
    $thirtyDt = DTWarehouse::where('assettype', AssetEnum::DT_thirty_three()->value)->count();
    $dtTotal = DTWarehouse::count();

    $getDTs = DTWarehouse::withCount('getCustomerCount')
            ->where('assettype', AssetEnum::DT_thirty_three()->value)
            ->paginate(20);

    $data =[
                'allDt' => $getDTs,
                'elevenDt' => $elevenDt,
                'thirtyDt' => $thirtyDt,
                'dtTotal' => $dtTotal,
    ];

    return $data;
   }


   


  



}
