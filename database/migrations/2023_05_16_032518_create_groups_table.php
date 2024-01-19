<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('added_by')->comment("user id who created group");
            $table->string('name');
            $table->string('color');
            $table->text('order_confirm_msg');
            $table->unsignedBigInteger('order_confirm_msg_lang');
            $table->text('order_default_note');
            $table->unsignedBigInteger('order_default_note_lang');
            $table->boolean('is_min_order_count')->default(false);
            $table->integer('min_items')->nullable();
            $table->integer('min_kegs')->nullable();
            $table->boolean('is_min_order_value')->default(false);
            $table->decimal('min_price', 8, 2)->nullable();
            $table->enum('tax_applicability', ['Applicable', 'Not Applicable'])->nullable();
            $table->enum('bill_deposits', ['Required', 'Not Required'])->nullable();
            $table->enum('order_approval', ['Automatic', 'Manual'])->nullable();
            $table->string('payment_method')->nullable();
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
        Schema::dropIfExists('groups');
    }
}
