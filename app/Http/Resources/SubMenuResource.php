<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\SubMenu;
use App\Models\MenuAccess;
use App\Models\Role;


class SubMenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_id' => MenuAccess::where("id", $this->menu_id)->value("menu_name"),
            'sub_menu_id' => SubMenu::where("id", $this->sub_menu_id)->value("id"),
            'sub_menu_name' => SubMenu::where("id", $this->sub_menu_id)->value("name"),
            'sub_menu_url' => SubMenu::where("id", $this->sub_menu_id)->value("menu_url"),
            'role_id' => Role::where("id", $this->role_id)->value("name"),
            'created_at' => $this->created_at,
        ];
       // return parent::toArray($request);
    }
}
