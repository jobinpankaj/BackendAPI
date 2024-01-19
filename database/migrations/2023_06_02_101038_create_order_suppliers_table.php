<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('retailer_id');
            $table->unsignedBigInteger('distributor_id');
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
        Schema::dropIfExists('order_suppliers');
    }
}
