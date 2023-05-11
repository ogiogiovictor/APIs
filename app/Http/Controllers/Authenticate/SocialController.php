<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Socialite;
use App\Models\User;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Adldap\Laravel\Facades\Adldap;


class SocialController extends BaseApiController
{
    //

    public function providerCallback(String $provider){

        return \Socialite::driver($provider)->redirect();
    }

    public function redirectoToProvider(String $provider){

        try {
           $social_user = \Socialite::driver($provider)->user();
         //$social_user = \Socialite::driver($provider)->stateless()->user();

            return $this->sendSuccess($social_user, "Authorization Successufully Generated", Response::HTTP_CREATED);

        }catch(\Exception $e){
            return $this->sendError("Error", $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }  //sudo yum install php-ldap

    public function authenticate(Request $request){

         
        return $user = Adldap::search()->users()->find('ogiogio victor');

        $credentials = $request->only('email', 'password');

        if(Auth::guard('ldap')->attempt($credentials)){
            $authUser = Auth::guard('ldap')->user();

            $success['Authorization'] = $authUser->createToken('Sanctom+Socialite')->plainTextToken;
            $success['user'] = $authUser;

            $token = $authUser->createToken('authToken')->accessToken;
            return $this->sendSuccess($success, "Authorization Successufully Generated", Response::HTTP_CREATED);
         }else {
            return $this->sendError('Invalid Login', "Check your credentials and try again", Response::HTTP_UNAUTHORIZED);
        }

    }
}
