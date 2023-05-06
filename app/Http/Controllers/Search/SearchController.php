<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\SearchFactory;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends BaseApiController
{
    public function searching(Request $request){

        try {
            $searchFactory = new SearchFactory();
            $search = $searchFactory->initalizeSearch($request);
            return $search->search();

        } catch (\InvalidArgumentException $e) {
         
            // handle the error message
            return $this->sendError("Invalid Search Type", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

   
    }
}
