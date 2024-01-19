<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserBillingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_billing_addresses', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger('user_id')->comment("id in users table");
            $table->text("address_to")->nullable()->comment("for retailer");
            $table->string("contact_email")->nullable()->comment("for retailer");
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
            $table->string("company_number_neq")->nullable()->comment("Company Number NEQ");
            $table->string("gst_registration_number")->nullable()->comment("GST Tax Registration number");
            $table->string("qst_registration_number")->nullable()->comment("QST Tax Registration number");
            $table->string("upload_business_certificate")->nullable()->comment("for retailer");
            $table->string("company_name")->nullable()->comment("for supplier");
            $table->string("upload_logo")->nullable()->comment("for supplier");
            $table->string("order_number_prefix")->nullable()->comment("for supplier");
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
        Schema::table('user_billing_addresses', function (Blueprint $table) {
            //
        });
    }
}
