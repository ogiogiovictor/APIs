<?php

namespace App\Http\Controllers\AlternatePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer\CustomerAuthModel;

class LogoutController extends Controller
{
    public function userLogout(Request $request){

        $userId = $request->Authorization;

        $user = CustomerAuthModel::where('Authorization', $userId)->first();

         // Check if the user exists
        if ($user) {
            // Delete all records with the same account number
            CustomerAuthModel::where('accountno', $user->accountno)->forceDelete();

            // Delete the user
            $user->forceDelete();

            return response()->json(['message' => 'Logged out successfully', 'user' => $userId]);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
        
       //  return response()->json(['message' => 'Logged out successfully', 'user' => $userId]);

    }
}



