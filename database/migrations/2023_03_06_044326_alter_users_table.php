<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string("user_reference_number")->nullable()->after("name");
            $table->string("phone_number")->nullable()->after("password");
            $table->text("address")->nullable()->after("phone_number");
            $table->string("country")->nullable()->after("address");
            $table->string("state")->nullable()->after("country");
            $table->string("city")->nullable()->after("state");
            $table->string("user_image")->nullable()->after("city");
            $table->unsignedInteger("user_type_id")->nullable()->after("user_image");
            $table->unsignedBigInteger("parent_id")->nullable()->after("user_type_id");
            $table->enum("status",["0","1"])->default("1")->comment("0=inactive, 1=active");
            $table->unsignedBigInteger("added_by")->nullable()->after("status");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
