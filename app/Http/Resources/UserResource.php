<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuRole;
use App\Models\MenuAccess;
use App\Models\SubMenu;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'authority' => $this->authority,
            "created_at" => Carbon::parse($this->created_at)->format('M d Y'),
            "role" => $this->roles->first()->name, //$this->roles->pluck('name')->toArray(), //$user->load('roles'), // Auth::user()->load('roles'),
             "status" => $this->status,
            "time_ago" => Carbon::parse($this->created_at)->subMinutes(2)->diffForHumans(),
            "menus" => $this->menuAccess($this->roles->first()->id),

        ];
        //return parent::toArray($request);
    }

    public function menuAccess($role) {

        return MenuRole::where("role_id", $role)->first()->menu_id;
    }

    
   
}
