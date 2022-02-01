<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDailyColumnToCartItemTable extends Migration
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
            $table->boolean('isDaily')->after('discountAmount')->default(false);
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
            $table->dropColumn('isDaily');
        });
    }
}
