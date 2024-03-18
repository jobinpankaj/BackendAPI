<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class BusinessCategoriesTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = ["Convenience Store", "Liquor Store", "Bar", "Cafe", "Restaurant", "Gas Station", "Department Store", "Super Market", "Store", "Casino", "Night Club", "Indiviual", "Event" ];
        foreach($arr as $val){
           
        DB::table('business_categories')->insert([
            'name' => $val,
            'description' => $val,
            "status" => "1"
        ]); 
        }
    }
}
