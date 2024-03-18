<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('id value from orders table');
            $table->unsignedBigInteger('order_supplier_id')->comment('id value from order_suppliers table');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_style_id');
            $table->unsignedBigInteger('product_format_id');
            $table->unsignedInteger('quantity');
            $table->decimal('price', 8, 2);
            $table->decimal('tax', 8, 2);
            $table->decimal('sub_total', 8, 2);
            $table->enum('status', ['0', '1', '2', '3', '4', '5'])->default('0')->comment('Order Status: 0-Pending, 1-Approved, 2-On Hold, 3-Shipped, 4-Delivered, 5-Cancelled');
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
        Schema::dropIfExists('order_items');
    }
}
