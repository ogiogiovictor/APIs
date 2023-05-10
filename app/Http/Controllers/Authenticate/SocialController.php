<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Socialite;
use App\Models\User;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SocialController extends BaseApiController
{
    //

    public function providerCallback(String $provider){

        return \Socialite::driver($provider)->redirect();
    }

    public function redirectoToProvider(String $provider){

        try {
          // $social_user = \Socialite::driver($provider)->user();
          $social_user = \Socialite::driver($provider)->stateless()->user();

            return $this->sendSuccess($social_user, "Authorization Successufully Generated", Response::HTTP_CREATED);

        }catch(\Exception $e){
            return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }  //sudo yum install php-ldap

    public function authenticate(Request $request){

        $credentials = $request->only('email', 'password');

        if(Auth::guard('ad')->attempt($credentials)){
            $authUser = Auth::guard('ad')->user();

            $success['Authorization'] = $authUser->createToken('Sanctom+Socialite')->plainTextToken;
            $success['user'] = $authUser;

            $token = $user->createToken('authToken')->accessToken;
            return $this->sendSuccess($success, "Authorization Successufully Generated", Response::HTTP_CREATED);
         }else {
            return $this->sendError('Invalid Login', "Check your credentials and try again", Response::HTTP_UNAUTHORIZED);
        }

    }
}
