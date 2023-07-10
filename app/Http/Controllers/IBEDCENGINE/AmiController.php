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
        

        $cachedSummary = Cache::remember($cacheKey, $minutes, function () use ($request) {
            $year = $request->year ?? Date('Y');
            $month = $request->month ?? Date('m');

            $getRequest = (new AmiService)->getMeterReading($year, $month); // $request->year, $request->month; and
            $newResource = AmiminiResource::collection($getRequest); // $request->year, $request->month; and
            return $newResource;
        });

          //  $getRequest = (new AmiService)->getMeterReading(2023, 5); // $request->year, $request->month; and

        //    $newResource = AmiminiResource::collection($getRequest); // $request->year, $request->month; and

        return $this->sendSuccess($cachedSummary, "Data Successfully Loaded - ", Response::HTTP_OK);
      
    }

    public function getAll(){
        Redis::flushall();

        $cacheKey = 'ami_event_getAll';
        $minutes = 5;

       /* $data = Cache::remember($cacheKey, $minutes, function () {
            $group = (new AmiService)->allConnectionsgroups();
            $getRequest = (new AmiService)->allConnections();

        return [
                'group' => $group,
                'ami_data' => $getRequest,
            ];

        });
        */

        $group = (new AmiService)->allConnectionsgroups();
        $getRequest = (new AmiService)->allConnections();

         $data = [
             'group' => $group,
             'ami_data' => $getRequest,
         ];
        return $this->sendSuccess($data, "Data Loaded - ". count($data), Response::HTTP_OK);
        
    }

    public function eventUpDown(){
        $requestPower = (new AmiService)->powerUppowerDown();
        return $this->sendSuccess($requestPower, "Data Loaded - ". count($requestPower), Response::HTTP_OK);
    }

    
}
