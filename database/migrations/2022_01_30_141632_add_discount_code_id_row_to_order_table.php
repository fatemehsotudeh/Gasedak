<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountCodeIdRowToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('discountCodeId')
                ->after('totalDiscountAmount')
                ->nullable();

            $table->dropColumn('discountCodeAmount');

            $table->foreign('discountCodeId')
                ->references('id')
                ->on('discountcodes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropForeign(['discountCodeId']);
            $table->dropColumn('discountCodeId');
            $table->string('discountCodeAmount')->after('totalDiscountAmount')->default(null);
        });
    }
}
