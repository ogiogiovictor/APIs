<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

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
}
