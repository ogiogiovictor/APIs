<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

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
        ];
        //return parent::toArray($request);
    }
}
