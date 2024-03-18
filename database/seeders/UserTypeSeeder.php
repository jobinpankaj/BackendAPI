<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => "Distributor",
                'description' => "Distributor as super admin",
                "status" => "1"
            ],
            [
                'name' => "Supplier",
                'description' => "Supplier as Liqour Company",
                "status" => "1"
            ],
            [
                'name' => "Retailer",
                'description' => "Retailers",
                "status" => "1"
            ],
            [
                'name' => "Delivery User",
                'description' => "Delivery users",
                "status" => "1"
            ]
        ];
        DB::table('user_types')->insert($data);
    }
}
