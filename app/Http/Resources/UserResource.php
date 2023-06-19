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
        //User Role
        $menu = MenuRole::where("role_id", $role)->first()->menu_id;
        
        //Remove the square bracket from the string
        $menuString = trim($menu, '[]');
        //Split the string into an array using commas as the delimiter
        $menuArray = explode(',', $menuString);
        //Convert the array values from string to integers
        $menuArray = array_map('intval', $menuArray);

        $menuName = MenuAccess::whereIn("id", $menuArray)->where("menu_status", "on")->get()->toArray();

        $menuIds = array_column($menuName, 'id');
        //$subMenus = SubMenu::whereIn(["menu_id" => $menuIds, "role_id" => $role])->get();
        $subMenus = SubMenu::whereIn("menu_id", $menuIds)->whereIn("role_id", [$role])->get();

         // User Permission
        $permission = array_map('intval', explode(',', trim(MenuRole::where("role_id", $role)->first()->permission_id, '[]')));
        $permissions = Permission::whereIn("id", $permission)->get()->keyBy('id');

        $result = [];
        foreach($menuName as $navbar){
            $submenu = $subMenus->where('menu_id', $navbar['id'])->toArray();

            $result[] = [
                'id' => $navbar['id'],
                'menu_name' => $navbar['menu_name'],
                'menu_url' => $navbar['menu_url'],
                'submenu' => $submenu,
               'permission' => $permissions->get($navbar['id'])
            ];

        }

        return $result;
       
    }

    
   
}
