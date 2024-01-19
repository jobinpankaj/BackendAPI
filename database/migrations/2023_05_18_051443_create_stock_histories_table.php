<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_id');
            $table->text('reason');
            $table->timestamp('datetime');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('quantity');
            $table->integer('new_stock');
            $table->enum('state', ['ok'])->default('ok');
            $table->string('lot_number')->nullable();
            $table->date('lot_date')->nullable();
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
        Schema::dropIfExists('stock_histories');
    }
}
