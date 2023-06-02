<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubMenu;

class SubMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

     protected $array = [
        [
            "name" => "Create Customer",
            "menu_url" => "createcustomer",
            "menu_id" => "1",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "Postpaid Customer",
            "menu_url" => "customers/postpaid",
            "menu_id" => "1",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "Prepaid Customer",
            "menu_url" => "customers/prepaid",
            "menu_id" => "1",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "Prepaid",
            "menu_url" => "payments",
            "menu_id" => "1",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "Bills",
            "menu_url" => "bills",
            "menu_id" => "1",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "All Customers",
            "menu_url" => "customers",
            "menu_id" => "1",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        




        [
            "name" => "Transformers",
            "menu_url" => "transformers",
            "menu_id" => "2",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "Feeders",
            "menu_url" => "feeders",
            "menu_id" => "2",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "Transmission",
            "menu_url" => "transmission",
            "menu_id" => "2",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],






        [
            "name" => "Complains",
            "menu_url" => "tickets",
            "menu_id" => "3",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],



        [
            "name" => "Events",
            "menu_url" => "mdacustomers",
            "menu_id" => "4",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "Events",
            "menu_url" => "amievents",
            "menu_id" => "4",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],






        [
            "name" => "Approval",
            "menu_url" => "crmdetails",
            "menu_id" => "5",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "CAAD",
            "menu_url" => "caad",
            "menu_id" => "5",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],



        [
            "name" => "Administration",
            "menu_url" => "allusers",
            "menu_id" => "6",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],

        [
            "name" => "Administration",
            "menu_url" => "locations",
            "menu_id" => "6",
            "role_id" =>  "",
            "permission_id" =>  ""
        ],
        
    ];
    public function run(): void
    {
        foreach($this->array as $array) { SubMenu::create($array); }
    }
}