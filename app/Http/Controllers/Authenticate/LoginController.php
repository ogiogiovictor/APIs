<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class LoginController extends BaseApiController
{
    public function login(LoginRequest $request): Object
    {

        if($request->expectsJson()) {

            $userStatus = User::where('email', $request->email)->value('status');

            if($userStatus == 0 || $userStatus == 'NULL'){
                return $this->sendSuccess('Invalid Status', "No Activation Included in the account", Response::HTTP_UNAUTHORIZED);
            }

            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $authUser = Auth::user();
                $success['Authorization'] = $authUser->createToken('Sanctom+Socialite')->plainTextToken;
                $success['user'] = $authUser;
                return $this->sendSuccess($success, "Authorization Successufully Generated", Response::HTTP_CREATED);
            }else {
                return $this->sendError('Invalid Login', "Check your credentials and try again", Response::HTTP_UNAUTHORIZED);
            }

        }else {
            return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    public function register(Request $request): Object {

        $postData = $request->validate([
            //$postData = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users,email',
                //'password' => ['required', 'min:8', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised(),]
                'password' => ['required', 'min:8']
  
            ]);     

            $user = $this->UserCreate($postData);

             //Atach User to a Role
           $user->assignRole('user');

           //Dispatch a Job to send Email


           $json = ['status' => Response::HTTP_OK, 'token' => $user->createToken('API Token')->plainTextToken];
           return response()->json($json, 200); 
      
    }


    private function UserCreate($postData){
        
        //Lets Create the User
       $user = User::create([
           'name' =>  $postData['name'],
           'email' => $postData['email'],
           'status' => 1,
           'authority' => 'user',
       ]);
       return $user;
   }


}
