<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTransferProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_transfer_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_transfer_id');
            $table->unsignedBigInteger('product_id');
            $table->string('batch')->nullable();
            $table->unsignedInteger('received')->default(0);
            $table->unsignedInteger('broken')->default(0);
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
        Schema::dropIfExists('inventory_transfer_products');
    }
}
