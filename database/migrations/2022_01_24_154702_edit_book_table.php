<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            //
            $table->dropColumn('percentDailyDiscountAmount');
            $table->dropColumn('dailyDiscountAmount');
            $table->boolean('isDailyDiscount')->after('percentDiscountAmount')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            //
            $table->integer('percentDailyDiscountAmount')->default(0)->after('percentDiscountAmount');
            $table->integer('dailyDiscountAmount')->default(0)->after('percentDailyDiscountAmount');
            $table->dropColumn('isDailyDiscount');
        });
    }
}
