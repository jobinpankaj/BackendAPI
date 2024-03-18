<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string("product_name");
            $table->unsignedBigInteger("style")->comment("id in product_styles table");
            $table->unsignedBigInteger("sub_category")->comment("id in sub-categories table");
            $table->enum("is_organic",["0","1"])->default("0")->comment("0=not organic, 1=organic");
            $table->double("alcohol_percentage",5,2);
            $table->string("product_image")->nullable();
            $table->string("label_image")->nullable();
            $table->string("combined_image")->nullable();
            $table->unsignedBigInteger('user_id')->comment("id in users table, Added By");
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
        Schema::dropIfExists('products');
    }
}
