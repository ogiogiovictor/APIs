<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    
    public function getUser(){
       
        if(!Auth::check()) {
            return $this->sendError("No Data", "Error Loading User Data", Response::HTTP_UNAUTHORIZED);
        }

        try{
            return $authUser = new UserResource(Auth::user()); 
        }catch(\Exception $e) {
            return $this->sendError("No Data", "Error Loading User Data", Response::HTTP_UNAUTHORIZED);
        }

        //return $authUser = new UserResource(Auth::user()); 

       // return $this->sendSuccess($user, "User Information", Response::HTTP_OK);
    }
}
