<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_reference')->nullable();
            $table->unsignedBigInteger('added_by');
            $table->string('added_by_user_type');
            $table->text('note')->nullable();
            $table->unsignedInteger('total_quantity');
            $table->decimal('total_amount', 8, 2);
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
        Schema::dropIfExists('orders');
    }
}
