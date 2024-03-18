<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 8, 2);
            $table->decimal('unit_price', 8, 2)->nullable();
            $table->unsignedBigInteger('tax_id');
            $table->decimal('tax_amount', 8, 2)->nullable();
            $table->boolean('suggest_retail_price')->default(false);
            $table->decimal('retail_unit_price', 8, 2)->nullable();
            $table->decimal('total_price', 8, 2)->nullable();
            $table->decimal('total_unit_price', 8, 2)->nullable();
            $table->decimal('total_retail_price', 8, 2)->nullable();
            $table->decimal('discount_percent', 8, 2)->nullable();
            $table->string('discount_name')->nullable();
            $table->enum('discount_type', ['percentage', 'dollars', 'special price'])->nullable();
            $table->integer('purchase_qty')->nullable();
            $table->boolean('is_minimum')->default(false);
            $table->string('discount_as_of')->nullable();
            $table->boolean('specific_audience')->default(false);
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
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
        Schema::dropIfExists('pricings');
    }
}
