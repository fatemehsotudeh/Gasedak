<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAvalibleFieldToCartItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cartItems', function (Blueprint $table) {
            //
            $table->boolean('isAvailable')->after('quantity')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cartItems', function (Blueprint $table) {
            //
            $table->dropColumn('isAvailable');
        });
    }
}
