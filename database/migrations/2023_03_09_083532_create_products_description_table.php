<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsDescriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_description', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->comment("id in products table");
            $table->text("description")->nullable();
            $table->text("public_description")->nullable();
            $table->unsignedBigInteger("language_id")->comment("id in site_languages table");
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
        Schema::dropIfExists('products_description');
    }
}
