<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VisibilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $visibilities = [
            [
                'name' => 'Visible for everybody'
            ],
            [
                'name' => 'Visible only by .....'
            ],
            [
                'name' => 'Invisible for .....'
            ],
            [
                'name' => 'Displayed as sold out'
            ],
            [
                'name' => 'Invisible for everybody'
            ]
        ];

        foreach($visibilities as $visibility)
        {
            DB::table('visibilities')->insert([
                'name' => $visibility['name'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
