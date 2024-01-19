<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetailerSupplierRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retailer_supplier_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('retailer_id')->comment("id in users table of retailer user type");
            $table->unsignedBigInteger('supplier_id')->comment("id in users table of supplier user type");
            $table->text("request_note");
            $table->enum("status",["0","1","2"])->default("2")->comment("0=> Decline, 1=> Accept, 2=> Pending");
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
        Schema::dropIfExists('retailer_supplier_requests');
    }
}
