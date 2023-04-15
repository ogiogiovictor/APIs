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

    
}
