<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\AssetEnum;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\AssetRepositoryInterface;
use Illuminate\Http\JsonResponse;
//use App\Models\ServiceUnit;
use Illuminate\Support\Facades\DB;

class ServiceController extends BaseApiController
{

    private $AssetRepository;

    public function __construct(AssetRepositoryInterface $AssetRepository){
        $this->AssetRepository = $AssetRepository;
    }

    public function index()
    {
         $getServiceUnits =  $this->AssetRepository->allServiceUnit();
        //$getServiceUnits = DB::connection("ace_db")->table("Acedata.dbo.ServiceUnits")->get();


        return $this->sendSuccess($getServiceUnits, "Service Unit Information Loaded Successfully", Response::HTTP_OK);
        
    }


    public function getType() {

        $serviceArray = [
            AssetEnum::DT_eleven()->value,
            AssetEnum::DT_thirty_three()->value
            
        ];

        return $this->sendSuccess($serviceArray, "Asset Information Loaded Successfully", Response::HTTP_OK);
    }
}
