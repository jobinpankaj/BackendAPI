<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type')->nullable()->after('product_name');
            $table->string('sap_lowbla')->nullable()->after('batch_number');
            $table->string('sap_metro')->nullable()->after('sap_lowbla');
            $table->string('sap_showbay')->nullable()->after('sap_metro');
            $table->unsignedBigInteger('product_format')->nullable()->after('sap_showbay');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'batch_number', 'sap_lowbla', 'sap_metro', 'sap_showbay']);
        });
    }
}
