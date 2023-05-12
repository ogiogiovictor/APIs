<?php

namespace App\Http\Controllers\ACE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\AssetRepositoryInterface;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\AssetRequest;
use App\Helpers\AssetHelper;
use App\Models\ZoneCustomer;
use App\Models\DimensionCustomer;
use App\Services\CustomerService;
use App\Enums\AssetEnum;



class AssetController extends BaseApiController
{

    private $AssetRepository;

    public function __construct(AssetRepositoryInterface $AssetRepository){
        $this->AssetRepository = $AssetRepository;
    }

    public function index(){

        $allDts = $this->AssetRepository->allDTs();
        return $this->sendSuccess($allDts, "Asset Information Loaded Successfully", Response::HTTP_OK);
       
    }

    public function store(AssetRequest $request){
       
        
        try {
            $request['AssetName'] = $request['DSS_11KV_415V_Name'];

            $assetData = AssetHelper::dataRequest($request);

            $storeAsset =  $this->AssetRepository->storeDTs($assetData, $request->assettype);
            return $this->sendSuccess($storeAsset, "Asset Information Saved Successfully", Response::HTTP_OK);

        }catch(\Exception $e){
            return $this->sendError($e->getmessage(), "No Result Found", Response::HTTP_BAD_REQUEST);
        }
       
    }

    public function stats() {

        $customers = (new CustomerService)->getWarehouseDashboard();

        return $this->sendSuccess($customers, "Asset Information Saved Successfully", Response::HTTP_OK);

    }

    public function getAssetWH(Request $request){

        if($request->type == AssetEnum::DT_eleven()->value){
            return $this->AssetRepository->getAllEleven();
        } else if($request->type == AssetEnum::DT_thirty_three()->value) {
            return $this->AssetRepository->getAllThirty();
        }else {
            return $this->AssetRepository->getAllDSSW();
        }


    }


   


    

    
}
