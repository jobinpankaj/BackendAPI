<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subCategories = [
            "Sobey's",
            "Loblaw",
            "Provigo",
            "Metro",
            "DBSQ",
            "Tite Frette",
            "Independent",
            "Bar and Resto",
            "Uncategorized"
        ];

        foreach($subCategories as $subCategory)
        {
            DB::table('sub_categories')->insert([
                'name' => $subCategory,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
