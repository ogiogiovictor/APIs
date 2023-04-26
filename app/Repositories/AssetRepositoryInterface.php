<?php

namespace App\Repositories;

Interface AssetRepositoryInterface
{
    
    public function allDTs();
    public function storeDTs($data, $assetType);
    //public function allFeeders();
    // public function storeFeeder($data, $assetType);
     public function allServiceUnit();
     public function getAllDSSW();
     public function getAllEleven();
     public function getAllThirty();
    // public function storeServiceUnit($data);
    // public function allInjectionStations();
    // public function storeInjectionStations($data);

    //Finding information
    // public function findDT($AssetID, $assetType);
    // public function findFeeder($AssetID, $assetType);
    // public function serviceUnit($id);

    //Updating Information
    // public function updateDT($data, $id, $assetType);
    // public function updateFeeder($data, $id, $assetType);


}
