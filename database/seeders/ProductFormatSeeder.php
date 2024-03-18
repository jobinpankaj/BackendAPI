<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductFormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productFormats = [
            [
                'name' => 'Bottle 250ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Bottle 250ml x 15',
                'unit' => 15
            ],
            [
                'name' => 'Bottle 250ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Bottle 250ml 6x3 pack',
                'unit' => 3
            ],
            [
                'name' => 'Bottle 250ml 6x4 pack',
                'unit' => 4
            ],
            [
                'name' => 'Bottle 250ml x a l\'unité',
                'unit' => 1
            ],
            [
                'name' => 'Bottle 100ml x 12',
                'unit' => 12    
            ],
            [
                'name' => 'Bottle 341ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Bottle 341ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Bottle 341ml x 4x6 pack',
                'unit' => 6
            ],
            [
                'name' => 'Bottle 341ml x 6x4 pack',
                'unit' => 4
            ],
            [
                'name' => 'Bottle ale 500ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Bottle ale 500ml x 15',
                'unit' => 15
            ],
            [
                'name' => 'Bottle ale 500ml x 20',
                'unit' => 20
            ],
            [
                'name' => 'Bottle ale 500ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Bottle sour 500ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Bottle sour 500ml x 15',
                'unit' => 15
            ],
            [
                'name' => 'Bottle sour 500ml x 20',
                'unit' => 20
            ],
            [
                'name' => 'Bottle sour 500ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Bottle 600ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Bottle 660ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Bottle 750ml brown x 12',
                'unit' => 12
            ],
            [
                'name' => 'Bottle 750ml brown x 6',
                'unit' => 6
            ],
            [
                'name' => 'Bottle 750ml clear x 12',
                'unit' => 12
            ],
            [
                'name' => 'Bottle 750ml clear x 6',
                'unit' => 6
            ],
            [
                'name' => 'Bottle 750ml green x 12',
                'unit' => 12
            ],
            [
                'name' => 'Bottle 750ml green x 6',
                'unit' => 6
            ],
            [
                'name' => 'Can 200ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Can 200ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Can 236ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Can 250ml x 4x6 pack',
                'unit' => 6
            ],
            [
                'name' => 'Can 250ml x 8',
                'unit' => 8
            ],
            [
                'name' => 'Can 300ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Can 330ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Can 330ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Can 355ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Can 355ml x 15',
                'unit' => 15
            ],
            [
                'name' => 'Can 355ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Can 355ml x 2x12 pack',
                'unit' => 12
            ],
            [
                'name' => 'Can 355ml x 3x8 pack',
                'unit' => 8
            ],
            [
                'name' => 'Can 355ml x30',
                'unit' => 30
            ],
            [
                'name' => 'Can 355ml sleek x 32',
                'unit' => 32
            ],
            [
                'name' => 'Can 355ml sleek x 36',
                'unit' => 36
            ],
            [
                'name' => 'Can 355ml x 4x6 pack',
                'unit' => 6
            ],
            [
                'name' => 'Can 355ml x 6x4 pack',
                'unit' => 4
            ],
            [
                'name' => 'Can 355ml unité',
                'unit' => 1
            ],
            [
                'name' => 'Can 440ml x12',
                'unit' => 12
            ],
            [
                'name' => 'Can 440ml x 24',
                'unit' => 12
            ],
            [
                'name' => 'Can 450ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Can 458ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Can 473ml x 1x4 pack',
                'unit' => 4
            ],
            [
                'name' => 'Can 473ml x 12',
                'unit' => 12
            ],
            [
                'name' => 'Can 473ml x 24',
                'unit' => 24
            ],
            [
                'name' => 'Can 473ml x 2x12 pack',
                'unit' => 12
            ],
            [
                'name' => 'Can 473ml x 6x4 pack',
                'unit' => 4
            ],
            [
                'name' => 'Can 473ml 4x6 pack',
                'unit' => 6
            ],
            [
                'name' => 'Can 473ml unité',
                'unit' => 1
            ],
            [
                'name' => 'Cask 20L',
                'unit' => 1
            ],
            [
                'name' => 'Cask 40L',
                'unit' => 1
            ],
            [
                'name' => 'Keg 20L',
                'unit' => 1
            ],
            [
                'name' => 'Keg 30L',
                'unit' => 1
            ],
            [
                'name' => 'Keg 50L',
                'unit' => 1
            ],
            [
                'name' => 'Keg 60L',
                'unit' => 1
            ],
            [
                'name' => 'Keg Bucké 20L',
                'unit' => 1
            ],
            [
                'name' => 'Keg Bucké 30L',
                'unit' => 1
            ],
            [
                'name' => 'Keg Bucké 50L',
                'unit' => 1
            ],
            [
                'name' => 'Keg 20L One Way',
                'unit' => 1
            ],
            [
                'name' => 'Keg 30L One Way',
                'unit' => 1
            ]
        ];

        foreach($productFormats as $format)
        {
            DB::table('product_formats')->insert([
                'name' => $format['name'],
                'unit' => $format['unit'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
