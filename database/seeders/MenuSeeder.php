<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MenuAccess;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

     protected $array = [
        [
            "menu_name" => "Customer",
            "menu_url" => "customer",
            "menu_status" =>  "on"
        ],
        [
            "menu_name" => "Asset",
            "menu_url" => "asset",
            "menu_status" =>  "on"
        ],

        [
            "menu_name" => "Customer Complaint",
            "menu_url" => "customer_complaint",
            "menu_status" =>  "on"
        ],
        [
            "menu_name" => "Events",
            "menu_url" => "events",
            "menu_status" =>  "on"
        ],

        [
            "menu_name" => "Approval",
            "menu_url" => "approval",
            "menu_status" =>  "on"
        ],

        [
            "menu_name" => "Administration",
            "menu_url" => "administration",
            "menu_status" =>  "on"
        ],
       
    ];

    public function run(): void
    {
        foreach($this->array as $array) { MenuAccess::create($array); }
    }
}
