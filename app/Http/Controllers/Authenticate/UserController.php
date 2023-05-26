<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
    }


    public function getAllUsers() {

        $users = User::paginate(20);
        // Modify the date format and status values
        $users->getCollection()->transform(function ($user) {
            // Convert created_at to human-readable date format
            $user->created_at = Carbon::parse($user->created_at)->format('Y-m-d H:i:s');
           // $user->created_at = Carbon::parse($user->created_at)->diffForHumans();

            // Convert status values to human-readable strings
            $user->status = $user->status == 1 ? 'Active' : 'Inactive';

            return $user;
        });

        return $this->sendSuccess($users, "Users Loaded", Response::HTTP_OK);

    }


    public function addUser(Request $request) {


        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users|max:255',
            'password' => 'required',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation Error", $validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'status' => 1,
            'authority' => $request->business_hub,
            'password' => Hash::make($request->password),
        ]);

          //Atach User to a Role
          $user->assignRole('admin');

        return $this->sendSuccess($user, "User Created Successfully", Response::HTTP_OK);
    }


}
