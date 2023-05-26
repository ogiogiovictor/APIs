<?php

namespace App\Repositories;

use App\Repositories\SearchRepositoryInterface;
use App\Models\DTWarehouse;
use App\Enums\AssetEnum;



class SearchAssetRepository implements SearchRepositoryInterface
{
  
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }


    public function search(){

        $search_term =  $this->request->DT;

        $customers = DTWarehouse::where(function ($query) use ($search_term) {
            //$searchQuery = $this->request->DT;

            $query->where('DSS_11KV_415V_Name', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('Assetid', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('DSS_11KV_415V_Owner', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('DSS_11KV_415V_Address', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('hub_name', 'LIKE', '%' .  $search_term . '%');
        })->paginate(100);

        $elevenDt = DTWarehouse::where(function ($query) use ($search_term) {
            //$searchQuery = $this->request->DT;

            $query->where('DSS_11KV_415V_Name', 'LIKE', '%' .  $search_term . '%')
            ->where('assettype', AssetEnum::DT_eleven()->value)
            ->orWhere('Assetid', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('DSS_11KV_415V_Owner', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('DSS_11KV_415V_Address', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('hub_name', 'LIKE', '%' .  $search_term . '%');
        })->count();

        $thirtyDt = DTWarehouse::where(function ($query) use ($search_term) {
            //$searchQuery = $request->searchQuery;
            $query->where('DSS_11KV_415V_Name', 'LIKE', '%' .  $search_term . '%')
            ->where('assettype', AssetEnum::DT_thirty_three()->value)
            ->orWhere('Assetid', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('DSS_11KV_415V_Owner', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('DSS_11KV_415V_Address', 'LIKE', '%' .  $search_term . '%')
            ->orWhere('hub_name', 'LIKE', '%' .  $search_term . '%');
        })->count();

        $dtTotal = DTWarehouse::count();


        $data =[
            'allDt' =>  $customers,
            'elevenDt' => $elevenDt,
            'thirtyDt' => $thirtyDt,
            'dtTotal' => $dtTotal,
        ];


        return $data;
    }

   

}
