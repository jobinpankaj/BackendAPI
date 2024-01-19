<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('recipient');
            $table->string('recipient_type',255);
            $table->string('recipient_name',255)->nullable();
            $table->unsignedBigInteger('inventory_id');
            $table->enum('status', [0, 1, 2])->default(2)->comment('0=Declined, 1=Accepted, 2=Pending');
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
        Schema::dropIfExists('inventory_transfers');
    }
}
