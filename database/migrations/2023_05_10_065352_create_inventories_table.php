<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->comment('id of supplier or distributor');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('distributor_id')->nullable();
            $table->string('batch')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('aisle');
            $table->string('aisle_name');
            $table->integer('shelf');
            $table->string('shelf_name');
            $table->tinyInteger('is_visible')->default(1);
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
        Schema::dropIfExists('inventories');
    }
}
