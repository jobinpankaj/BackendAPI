<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment("id in users table");
            $table->string("business_name")->comment("for retailer")->nullable();
            $table->string("group_name")->comment("for retailer")->nullable();
            $table->unsignedBigInteger("business_category_id")->nullable()->comment("id in business_categories table");
            $table->string("contact_email")->nullable();
            $table->string("public_phone_number")->comment("for retailer")->nullable();
            $table->string("phone_number")->nullable();
            $table->string("contact_name")->nullable();
            $table->string("website_url")->nullable();
            $table->string("office_number",20)->nullable();
            $table->enum("opc_status",["0","1"])->default("0")->comment("0=not, 1= yes");
            $table->enum("home_consumption",["0","1"])->default("0")->comment("0=not, 1= yes");
            $table->enum("alcohol_permit",["0","1"])->default("0")->comment("0=not, 1= yes");
            $table->string("company_name")->comment("for supplier")->nullable();
            $table->string("alcohol_production_permit")->comment("for supplier")->nullable();
            $table->string("alcohol_production_permit_image")->comment("for supplier")->nullable();
            $table->enum("business_name_status",["0","1"])->default("0")->comment("0=not show, 1=show");
            $table->enum("distribution_bucket_status",["0","1"])->default("0")->comment("distributed by distribution bucket");
            $table->enum("have_product_status",["0","1"])->default("0")->comment("have product but don't produce");
            $table->enum("agency_sell_and_collect_status",["0","1"])->default("0")->comment("agency, sell & collect on behalf of suppliers");
            $table->enum("produce_product_status",["0","1"])->default("0")->comment("produce product and authorize Buvons to distribute and collect");
            $table->enum("status",["0","1"])->default("1")->comment("0=inactive, 1=active");
            $table->enum("order_type",["1","2","3"])->default("3")->comment("1 => take order for delivery, 2=> take order for pick, 3=> both");
            $table->integer('alcohol_production_limit')->nullable()->comment('for supplier (in hectolitre)');
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
        Schema::table('user_profiles', function (Blueprint $table) {
            //
        });
    }
}
