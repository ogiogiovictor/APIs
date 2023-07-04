<?php

namespace App\Http\Controllers\ACE;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Repositories\AssetRepositoryInterface;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\AssetRequest;
use App\Helpers\AssetHelper;
use App\Models\ZoneCustomer;
use App\Models\DimensionCustomer;
use App\Models\DTWarehouse;
use App\Models\BillingEffiency;
use App\Services\CustomerService;
use App\Enums\AssetEnum;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\BillingEfficencyResource;
use App\Http\Resources\DTBusinessHubResource;


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

        $cacheKey = 'warehouse_dashboard_stats';
        $minutes = 5;

        $customers = Cache::remember($cacheKey, $minutes, function () {
            return (new CustomerService)->getWarehouseDashboard();
        });

       // $customers = (new CustomerService)->getWarehouseDashboard();

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



    public function dtBillingEffiency(){
     

       //$dtEfficenty = BillingEffiency::orderby("TotalDSS", "desc")->paginate(30);
      // $dtStatus =  DTWarehouse::select('Status', DB::raw('COUNT(Assetid) as AssetCount'))->groupBy('Status')->get();

       $cacheKey = 'warehouse_billing_efficiency';
        $minutes = 30;

        $data = Cache::remember($cacheKey, $minutes, function () {
           return  [
               'dt_billing' => BillingEfficencyResource::collection(BillingEffiency::orderby("TotalDSS", "desc")->paginate(30)),
               // 'dt_billing' => BillingEffiency::orderby("TotalDSS", "desc")->paginate(30),
                'dt_by_status' => DTWarehouse::select('Status', DB::raw('COUNT(Assetid) as AssetCount'))->groupBy('Status')->get(),
                'dt_total_billed_with_value' => BillingEffiency::where('TotalCustomers', '<>', 0)->count(),
                'dt_billed_dss' => BillingEffiency::where('TotalDSS', '<>', 0)->count(),
               ];
        });


    //    $data = [
    //     'dt_billing' => $dtEfficenty,
    //     'dt_by_status' => $dtStatus
    //    ];

        return $this->sendSuccess($data, "Asset Information Saved Successfully", Response::HTTP_OK);
    }




    public function DTBusinessHub() {

        //count(Assetid), hub_name,
        $getDSS = DTBusinessHubResource::collection(DTWarehouse::select("hub_name", DB::raw("COUNT(Assetid) as asset_count"))->groupBy('hub_name')->get());

        return $this->sendSuccess($getDSS, "loaded Successfully", Response::HTTP_OK);

    }



    



   


    

    
}
