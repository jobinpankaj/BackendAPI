<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserShippingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_shipping_addresses', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger('user_id')->comment("id in users table");
            $table->time("delivery_time")->nullable()->comment("for retailer");
            $table->text("delivery_notes")->comment("for retailer");
            $table->string("contact_name")->nullable()->comment("for retailer");
            $table->string("phone_number")->nullable()->comment("for retailer");
            $table->string("address_1")->nullable();
            $table->text("place_id")->nullable();
            $table->double("latitude",10,7)->nullable();
            $table->double("longitude",10,7)->nullable();
            $table->string("address_2")->nullable();
            $table->string("city")->nullable();
            $table->string("postal_code")->nullable();
            $table->string("state")->nullable();
            $table->string("country")->nullable();
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
        Schema::table('user_shipping_addresses', function (Blueprint $table) {
            //
        });
    }
}
