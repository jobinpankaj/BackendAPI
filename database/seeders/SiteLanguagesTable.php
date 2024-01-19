<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class SiteLanguagesTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('site_languages')->insert(
        [
            'name' => "English",
            'short_code' => "en",
            "status" => "1"
        ],[
            'name' => "French",
            'short_code' => "fr",
            "status" => "1"
        ]
        );
    }
}
