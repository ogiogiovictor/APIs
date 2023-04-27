<?php

namespace App\Http\Controllers\OPS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OpsDisconnection;

class Disconnection extends Controller
{
    public function index(){

        return OpsDisconnection::paginate(20);
    }
}
