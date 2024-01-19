<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDwcRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dwc_routes', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("driver_name",255)->nullable();
            $table->text("truck_details")->nullable();
            $table->text("start_address");
            $table->double("start_latitude",10,7);
            $table->double("start_longitude",10,7);
            $table->text("end_address");
            $table->double("end_latitude",10,7);
            $table->double("end_longitude",10,7);
            $table->integer("minimun_number_of_items");
            $table->enum("minimum_per_delivery_status",["0","1"])->default("0")->comment("0=no, 1=yes");
            $table->unsignedBigInteger("user_id")->comment("id in users table");
            $table->enum("status",["0","1"])->default("1")->comment("0=inactive, 1=active");
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
        Schema::dropIfExists('dwc_routes');
    }
}
