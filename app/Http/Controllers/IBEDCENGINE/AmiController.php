<?php

namespace App\Http\Controllers\IBEDCENGINE;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\AmiRequest;
use App\Services\AmiService;
use App\Http\Resources\AmiResource;
use App\Http\Resources\AmiminiResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class AmiController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     */
    public function store(AmiRequest $request): JsonResponse
    {
        if($request->expectsJson()) {

            $getRequest = (new AmiService)->getConnection($request->DATE);

            return $this->sendSuccess(AmiResource::collection($getRequest), "Data Successfully Loaded - ". count($getRequest), Response::HTTP_OK);
        }else {
            return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     /**
     * Display the loadSummary resource.
     */
    public function loadSummary(Request $request): JsonResponse
    {

        if($request->expectsJson()) {

            $getRequest = (new AmiService)->getSummary($request);

            return $this->sendSuccess($getRequest, "Data Successfully Loaded - ". count($getRequest), Response::HTTP_OK);
        }else {
            return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getSummary(Request $request){
        
        
        $cacheKey = 'ami_event_summary_mdacustomers';
        $minutes = 5;

        $eventType = $request->query('type'); 
        

        $cachedSummary = Cache::remember($cacheKey, $minutes, function () use ($request, $eventType) {
            $year = $request->year ?? Date('Y');
            $month = $request->month ?? Date('m');

            $group = (new AmiService)->allConnectionsgroups();
            $getRequest = (new AmiService)->getMeterReading($year, $month, $eventType); // $request->year, $request->month; and
            $newResource = AmiminiResource::collection($getRequest); // $request->year, $request->month; and

            return [
                'group' => $group,
                'summary_data' => $newResource,
            ];
        });

    

        return $this->sendSuccess($cachedSummary, "Data Successfully Loaded - ", Response::HTTP_OK);
      
    }

    public function getAll(Request $request){
        //Redis::flushall();

        $cacheKey = 'ami_event_getAll';
        $minutes = 5;

        $eventType = $request->query('type'); 

        $data = Cache::remember($cacheKey, $minutes, function () use ($eventType) {
            $group = (new AmiService)->allConnectionsgroups();
            $getRequest = (new AmiService)->allConnections($eventType);
    
            return [
                'group' => $group,
                'ami_data' => $getRequest,
            ];
        });
        

        

      /*  $group = (new AmiService)->allConnectionsgroups();
        $getRequest = (new AmiService)->allConnections($eventType);

         $data = [
             'group' => $group,
             'ami_data' => $getRequest,
         ];
         */
        return $this->sendSuccess($data, "Data Loaded - ". count($data), Response::HTTP_OK);
        
    }

    public function eventUpDown(){
        $requestPower = (new AmiService)->powerUppowerDown();
        return $this->sendSuccess($requestPower, "Data Loaded - ". count($requestPower), Response::HTTP_OK);
    }




      /**
     * Display the loadSummary resource.
     */
    public function monthlySummary(): JsonResponse
    {
            try {
                $getRequest = (new AmiService)->getMonthlySummary();
               // $getRequest = (new AmiService)->amiMonthlySummary();
                return $this->sendSuccess($getRequest, "Data Successfully Loaded - ". count($getRequest), Response::HTTP_OK);
            }catch(\Exception $e){
                return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

    }



     /**
     * Display the loadSummary resource.
     */
    public function FeederDetails(Request $request): JsonResponse
    {
            try {

                $eventType = $request->query('type'); 

                $getRequest = (new AmiService)->getAmiFeeders($eventType);
                $group = (new AmiService)->getAMIFeederGroup();

                $data = [
                    'group' => $group,
                    'data' => $getRequest
                ];

                return $this->sendSuccess($data, "Data Successfully Loaded - ". count($getRequest), Response::HTTP_OK);
            }catch(\Exception $e){
                return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
            }

    }

    
}
