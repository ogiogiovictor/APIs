<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    
    public function getUser(){
        $user = Auth::user();
        return $this->sendSuccess($user, "User Information", Response::HTTP_OK);
    }
}
