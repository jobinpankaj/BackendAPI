<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $taxes = [
            [
                'name' => 'Tax Exempt',
                'tax' => 0
            ],
            [
                'name' => 'GST 5%',
                'tax' => 5
            ],
            [
                'name' => 'QST 10%',
                'tax' => 10
            ],
            [
                'name' => 'GST 10% QST 10%',
                'tax' => 20
            ]
        ];

        foreach($taxes as $tax)
        {
            DB::table('taxes')->insert([
                'name' => $tax['name'],
                'tax' => $tax['tax'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
