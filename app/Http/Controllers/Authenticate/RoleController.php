<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\MenuRole;

class RoleController extends BaseApiController
{
    public function index(){

        $roles = Role::all();
        return $this->sendSuccess($row, "Roles Information", Response::HTTP_OK);

    }

    public function store(Request $request) {

        $validate = $request->validate(['name' => ['required', 'min:3'] ]);
        $newRole = Role::create([$validate]);

        try{
            return $this->sendSuccess($newRole, "Roles Information", Response::HTTP_OK);
        }catch(\Exception $e){
            return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }

    public function update(Request $request, Role $permission) {

        $validate = $request->validate(['name' => ['required'] ]);
        $newRole = Role::update([$validate]);

        try{
            return $this->sendSuccess($newRole, "Roles Information", Response::HTTP_OK);
        }catch(\Exception $e){
            return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function assignMenuRole(Request $request){

        $validate = $request->validate([
            'role_id' => ['required'],
            'menu_id' => 'required',
        ]);


        try{

            //Check if row exist if "YES" update if "NO" insert
           
            $newRole = MenuRole::updateOrCreate(
            ['role_id' => $validate['role_id']],    
            [
                'role_id' => $validate['role_id'],
                'menu_id' => $validate['menu_id'],
                'permission_id' => $request->permission_id

            ]);

            return $this->sendSuccess($newRole, "Permission Successfully Added", Response::HTTP_OK);
        }catch(\Exception $e){
            return $this->sendError("Error", $e, Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }
}
