<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer\CustomerAuthModel;

class LogoutController extends Controller
{
    public function userLogout(Request $request){

        $userId = $request->Authorization;

        $deleteForce = CustomerAuthModel::where('Authorization', $userId)->get();

        foreach ($deleteForce as $user) {
            $user->forceDelete();
        }

        
 
        //$user =  auth()->user()->tokens()->delete();
     
         
         return response()->json(['message' => 'Logged out successfully', 'user' => $userId]);

    }
}
