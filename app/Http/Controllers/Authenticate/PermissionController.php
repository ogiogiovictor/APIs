<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionController extends BaseApiController
{
    public function index(){

        $roles = Permission::all();
        return $this->sendSuccess($row, "Roles Information", Response::HTTP_OK);

    }

    public function store(Request $request) {

        $validate = $request->validate(['name' => ['required', 'min:3'] ]);
        $newRole = Permission::create([$validate]);

        try{
            return $this->sendSuccess($newRole, "Roles Information", Response::HTTP_OK);
        }catch(\Exception $e){
            return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        

    }

    public function update(Request $request, Permission $permission) {

        $validate = $request->validate(['name' => ['required'] ]);
        $newRole = Permission::update([$validate]);

        try{
            return $this->sendSuccess($newRole, "Roles Information", Response::HTTP_OK);
        }catch(\Exception $e){
            return $this->sendError("Error", "Error Loading Data, Something went wrong", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }




}
