<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Models\DTWarehouse;
use Illuminate\Support\Facades\Auth;

class GeneralService
{

    public function getSpecialRole(){
        return $roleName = ['project_office', 'billing', 'cfo', 'coo', 'admin', 'md'];
    }

    public function getUserLevelRole(){

        $role_name = Auth::user()->roles->pluck('name')->first();
        $user = Auth::user();
        $checkLevel = Auth::user()->level;

        $values = explode(",", $checkLevel);  // ->where("service_center", $serviceCenter)
        $region = $values[0] ?? '';
        $businessHub = $values[1] ?? '';
        $serviceCenter = $values[2] ?? '';

        return [
            'role' => $role_name,
            'userObject' => $user,
            'level' => $checkLevel,
            'region' => $region,
            'business_hub' => $businessHub,
            'sc' => $serviceCenter
        ];
    }



}