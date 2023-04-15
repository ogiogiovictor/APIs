<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    
    public function getUser(){
       
        return $authUser = new UserResource(Auth::user()); 

       // return $this->sendSuccess($user, "User Information", Response::HTTP_OK);
    }
}
