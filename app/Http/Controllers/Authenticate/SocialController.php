<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Ldap\AlphaUser;
use App\Http\Requests\ADRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\BTransaction;

class SocialController extends BaseApiController
{
    //

   

    public function authenticate(ADRequest $request){

         // Finding a user:
      //  return $user = Adldap::search()->users()->find('victor.ogiogio@ibedc.com');
        
      if($request->expectsJson()) {

           $validatedData = $request->validated();

            Auth::shouldUse("alpha");

            if (Auth::attempt($validatedData)) {

                $user = Auth::user();

                $checkType = $this->checkExistEmail($user->mail, $validatedData['password']);

                $data = [
                    'user' => $checkType,
                    'ad_user' => $user
                ];
                return $data;

            }else if(Auth::guard('web')->attempt(['email' => $validatedData['mail'], 'password' => $validatedData['password'] ])) {
                $authUser = Auth::guard('web')->user();
                $success['Authorization'] = $authUser->createToken('Sanctom+Socialite')->plainTextToken;
                $success['user'] = $authUser;
               
                return $this->sendSuccess($success, "Authorization Successufully Generated", Response::HTTP_CREATED);
            }
            
            

      }
            


    }



    private function checkExistEmail($email, $password){

        $userStatus = User::where('email', $email)->first();

        if($userStatus && $userStatus->status == 1){
            $user = Auth::user();
            $token = Str::random(80);
            $user->api_token = hash('sha256', $token);
            $success['Authorization'] = $token;
            $success['user'] = $userStatus ;

            return $this->sendSuccess($success, "Authorization Successufully Generated", Response::HTTP_CREATED);

        }else if($userStatus && $userStatus->status == 0){

            return $this->sendError('Access Denied', "You do not have access to use this application", Response::HTTP_UNAUTHORIZED);

        }else if(!$userStatus){

            $user = Auth::user();
            return $this->sendSuccess($user, "Authorization Successufully Generated", Response::HTTP_CREATED);

            //We need to create the user and retur the user with the token.
            $user = User::create([
                'name' =>  $user->displayname,
                'email' => $user->mail,
                'status' => 1,
                'authority' => 'user',
                'login_type' => 'active_directory',
                'password' => hash('sha256', Str::random(30)),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'domain' => $user->userprincipalname,
                'guid' => $user->description,
            ]);

        } else{
                return $this->sendError('Invalid Login', "Check your credentials and try again", Response::HTTP_UNAUTHORIZED);
        } 
        
      

    }



 


}
