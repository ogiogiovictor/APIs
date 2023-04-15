<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

     protected $array = [
        [ 'name' => 'user'],
        [ 'name' => 'cro'],
        [ 'name' => 'teamlead'],
        [ 'name' => 'businesshub_manager'],
        [ 'name' => 'regional_manager'],
        [ 'name' => 'manager'],
        [ 'name' => 'rpo'],
        [ 'name' => 'project_officer'],
        [ 'name' => 'billing'],
        [ 'name' => 'technical_engineer'],
        [ 'name' => 'accountant'],
        [ 'name' => 'ccu'],
        [ 'name' => 'ceo'],
        [ 'name' => 'cfo'],
        [ 'name' => 'coo'],
        [ 'name' => 'senior_manager'],
        [ 'name' => 'admin'],
        [ 'name' => 'md'],
        [ 'name' => 'third_party_app'],
    ];

    public function run(): void
    {
        foreach($this->array as $array) { Role::create($array); }
    }
}
