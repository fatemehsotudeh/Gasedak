<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSomeColumnsFromBookTable extends Migration
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
            //$table->dropForeign(['storeId']);
            //$table->dropColumn('storeId');
            if (Schema::hasColumn('books', 'inventory')) {
                $table->dropColumn('inventory');
            }
            if (Schema::hasColumn('books', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('books', 'discountAmount')) {
                $table->dropColumn('discountAmount');
            }
            if (Schema::hasColumn('books', 'percentDiscountAmount')) {
                $table->dropColumn('percentDiscountAmount');
            }
            if (Schema::hasColumn('books', 'isDailyDiscount')) {
                $table->dropColumn('isDailyDiscount');
            }
            if (Schema::hasColumn('books', 'dailyDiscountCreatedDate')) {
                $table->dropColumn('dailyDiscountCreatedDate');
            }

            if (Schema::hasColumn('books', 'dailyDiscountExpDate')) {
                $table->dropColumn('dailyDiscountExpDate');
            }

            if (!Schema::hasColumn('books', 'ableEditId')) {
                $table->unsignedBigInteger('ableEditId')->nullable()->after('id');

                $table->foreign('ableEditId')
                    ->references('id')
                    ->on('stores');
            }
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
//            $table->dropForeign(['ableEditId']);
//            $table->dropColumn('ableEditId');
//            $table->unsignedBigInteger('storeId');
            if (!Schema::hasColumn('books', 'inventory')) {
                $table->integer('inventory');
            }
            if (!Schema::hasColumn('books', 'price')) {
                $table->integer('price');
            }
            if (!Schema::hasColumn('books', 'discountAmount')) {
                $table->integer('discountAmount');
            }
            if (!Schema::hasColumn('books', 'percentDiscountAmount')) {
                $table->integer('percentDiscountAmount')->default(0);
            }
            if (!Schema::hasColumn('books', 'isDailyDiscount')) {
                $table->boolean('isDailyDiscount')->default(0);
            }
            if (!Schema::hasColumn('books', 'dailyDiscountCreatedDate')) {
                $table->timestamp('dailyDiscountCreatedDate')->nullable();
            }
            if (!Schema::hasColumn('books', 'dailyDiscountExpDate')) {
                $table->timestamp('dailyDiscountExpDate')->nullable();
            }

        });
    }
}
