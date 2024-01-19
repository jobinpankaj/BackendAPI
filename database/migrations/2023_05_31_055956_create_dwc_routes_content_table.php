<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDwcRoutesContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dwc_routes_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("dwc_route_id");
            $table->text("description");
            $table->text("message");
            $table->unsignedBigInteger("site_language_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dwc_routes_content');
    }
}
