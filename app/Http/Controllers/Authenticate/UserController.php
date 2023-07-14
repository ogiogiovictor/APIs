<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\MenuAccess;
use App\Models\MenuRole;
use App\Models\SubMenu;
use App\Models\User;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\AssignSubMenu;


class UserController extends BaseApiController
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
            'status' => "1",
            'authority' => $request->authority,
            'password' => Hash::make($request->password),
            'level' => $request->level ?? []
        ]);

        // $request->business_hub, $request->region, $request->service_center

          //Atach User to a Role
          //$user->assignRole('admin');
          $user->assignRole($request->role);

        return $this->sendSuccess($user, "User Created Successfully", Response::HTTP_OK);
    }




    public function getAccess() {

        $userRole = Auth::user()->roles->pluck('id')->first();

        //Check if the user Role have access to the menu/submenu
        $menuRole = MenuRole::where('role_id', $userRole)->get();
        $userResource = new UserResource(Auth::user());
        $userResource->menuAccess($userRole);

        $menuRole = MenuRole::where('role_id', $userRole)->first()->menu_id;
        $menuString = array_map('intval', explode(',', trim($menuRole, '[]')));

      // $hasAccess = SubMenu::whereIn('menu_id', $menuString)->get();
        $hasAccess = SubMenu::whereIn("menu_id", $menuString)->where("role_id", $userRole)->get();

        return $this->sendSuccess($hasAccess, "Successfully", Response::HTTP_OK);
    }


    public function AccessControl() {
        $getMenu = MenuAccess::where("menu_status", "on")->get();
        $getSubMenu = SubMenu::all();

        $array = [];
        foreach($getMenu as $get){
            $array[] = [
                'menu_id' => $get->id,
                'menu_name' => $get->menu_name,
                'menu_status' => $get->menu_status,
                'submenu' => SubMenu::where('menu_id', $get->id)->get()
            ];
        }

        return $this->sendSuccess($array, "Customer Approved Successfully", Response::HTTP_OK);
     }


     public function getRolePermission($role_id) {

        //$hasAccess = SubMenu::whereIn("role_id", [strval($role)])->get();
        $hasAccess =  $this->refactorOutput(AssignSubMenu::where("role_id", $role_id)->get()); 

        return $this->sendSuccess($hasAccess, "Successfully", Response::HTTP_OK);
    }



    private function refactorOutput($data){
        if($data){
            $array = [];
            foreach($data as $get){
                $array[] = [
                    'created_at' => $get->created_at,
                    'id' => intval($get->sub_menu_id),
                    'menu_id' => intval($get->menu_id),
                    'sub_menu_id' => intval($get->sub_menu_id),
                    'name' => SubMenu::where("id", $get->sub_menu_id)->value("name"),

                    'menu_status' => SubMenu::where("id", $get->sub_menu_id)->value("menu_status"),
                    'menu_url' =>SubMenu::where("id", $get->sub_menu_id)->value("menu_url"),
                   
                    'role_id' =>   $get->role_id
                ];
            }
            return $array;
        }

     }



    public function AssignUserMenu(Request $request){

        $getRowID = Role::where('name', $request->role)->first();
        $subMenu = $request->submenu_id;
        $new_array = SubMenu::whereIn("id", $subMenu)->pluck('menu_id')->toArray();

       // $menuIds = implode(',', $request->menu_id);
        $menuIds = implode(',', array_unique(array_map('intval', $new_array)));

        $updateMenuRole = MenuRole::updateOrCreate(
            ['role_id' => $getRowID->id],    
            [
                'menu_id' =>  "[$menuIds]",

            ]);
        
       //You need to insert based on the role and submenu
      if ($subMenu) {
            foreach ($subMenu as $newGet) {
               
                AssignSubMenu::updateOrCreate(
                    ['role_id' => $getRowID->id, 'sub_menu_id' => $newGet],
                    [
                        'menu_id' => SubMenu::where("id", $newGet)->value("menu_id"),
                        'role_id' => $getRowID->id
                    ]
                );
            }
     }
    
      

        if($updateMenuRole){
            return $this->sendSuccess($updateMenuRole, "Record Successfully Updated", Response::HTTP_OK);
        }else {
            return $this->sendError("Error", "No Result Found", Response::HTTP_BAD_REQUEST);
        }
       

    }


    public function userLogout(Request $request){

        $userId = $request->userId;
       if(!Auth::check()) {
        return $this->sendError("No Data", "Error Loading User Data", Response::HTTP_UNAUTHORIZED);
        }

        $user =  auth()->user()->tokens()->delete();
    
        
        return response()->json(['message' => 'Logged out successfully', 'user' => $user]);
    }

    






}
