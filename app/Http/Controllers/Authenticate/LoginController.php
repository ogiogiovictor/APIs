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
}
