<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailabilityGroupMaximumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('availability_group_maximums', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('availability_id');
            $table->unsignedBigInteger('group_id');
            $table->integer('maximum_quantity');
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
        Schema::dropIfExists('availability_group_maximums');
    }
}
