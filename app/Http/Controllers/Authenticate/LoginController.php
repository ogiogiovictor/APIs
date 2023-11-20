<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class LoginController extends BaseApiController
{
    public function login(LoginRequest $request)
    {

        if($request->expectsJson()) {

             $validatedData = $request->validated();


            $userStatus = User::where('email', $validatedData['email'])->value('status');

            if($userStatus == 0 || $userStatus == '0'){
                return $this->sendError('Invalid Status', "No Activation Included in the account", Response::HTTP_UNAUTHORIZED);
            }

            if(Auth::attempt(['email' => $validatedData['email'], 'password' => $validatedData['password'] ])){
                
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



    public function forgotPassword(Request $request){

            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendError("Error", "We can't find a user with that e-mail address.", Response::HTTP_BAD_REQUEST);
            }

            $status = Password::sendResetLink($request->only('email') );

        /* return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
            */

            $neStatus = Password::RESET_LINK_SENT;

            $getToken = DB::table('password_reset_tokens')->where('email', $request->email)->first();

            return $this->sendSuccess($getToken, "Password changed Sent !", Response::HTTP_OK);

        }


        public function changePassword(Request $request){

            $request->validate([
                'old_password' => 'required',
                'new_password' => 'required|string|min:6',
            ]);
    
            $user = Auth::user();
    
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->sendError("Error", "Your current password does not matches with the password you provided. Please try again.", Response::HTTP_BAD_REQUEST);
            }
    
            if (strcmp($request->old_password, $request->new_password) == 0) {
                return $this->sendError("Error", "New Password cannot be same as your current password. Please choose a different password.", Response::HTTP_BAD_REQUEST);
            }
    
            //Hash::make($request->new_password)
            $user->password = bcrypt($request->new_password);
            $user->save();
    
            return $this->sendSuccess($user, "Password changed successfully !", Response::HTTP_OK);
        }



        public function resetPassword(Request $request) {

            $request->validate([
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([ 'password' => Hash::make($password) ])->setRememberToken(Str::random(60));
         
                    $user->save();
         
                    event(new PasswordReset($user));
            });

            $status === Password::PASSWORD_RESET;
            
        }


        public function checkPassword($checkpassword){
            if(!$checkpassword){
                return $this->sendError("Error", "We can't find a user with that e-mail address.", Response::HTTP_BAD_REQUEST);
            } 

            $getToken = DB::table('password_reset_tokens')->where('token', $checkpassword)->first();

            if($getToken) {
                return $this->sendSuccess($getToken, "Token Exists", Response::HTTP_OK);
            }else {
                return $this->sendError("Error", "We can't find a user with that e-mail address.", Response::HTTP_BAD_REQUEST);
            }
        }



        public function integrateSMS(Request $request){
            
        }

}
