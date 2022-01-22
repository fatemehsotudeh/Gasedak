<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyDiscountCoulumnsToBookTable extends Migration
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
            $table->integer('percentDailyDiscountAmount')->default(0)->after('percentDiscountAmount');
            $table->integer('dailyDiscountAmount')->default(0)->after('percentDailyDiscountAmount');
            $table->timestamp('dailyDiscountCreatedDate')->after('dailyDiscountAmount')->nullable();
            $table->timestamp('dailyDiscountExpDate')->after('dailyDiscountCreatedDate')->nullable();

            $table->charset='utf8';
            $table->collation='utf8_general_ci';
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
            $table->dropColumn('percentDailyDiscountAmount');
            $table->dropColumn('dailyDiscountAmount');
            $table->dropColumn('dailyDiscountCreatedDate');
            $table->dropColumn('dailyDiscountExpDate');
        });
    }
}
