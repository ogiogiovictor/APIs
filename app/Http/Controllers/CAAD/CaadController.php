<?php

namespace App\Http\Controllers\CAAD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Caad;

class CaadController extends BaseApiController
{
    public function getApproval(){

        $getAll = Caad::all();

        return $this->sendSuccess($getAll, "Get All Results", Response::HTTP_CREATED);

    }
}
