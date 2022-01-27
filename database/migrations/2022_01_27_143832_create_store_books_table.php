<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storeBooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('storeId');
            $table->unsignedBigInteger('bookId');
            $table->integer('inventory');
            $table->integer('price');
            $table->integer('discountAmount')->default(0);
            $table->integer('percentDiscountAmount')->default(0);
            $table->boolean('isDailyDiscount')->default(0);
            $table->timestamp('dailyDiscountCreatedDate')->nullable();
            $table->timestamp('dailyDiscountExpDate')->nullable();
            $table->boolean('exhibition')->default(false);
            $table->timestamps();

            $table->foreign('storeId')
                ->references('id')
                ->on('stores');

            $table->foreign('bookId')
                ->references('id')
                ->on('books');

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
        Schema::dropIfExists('storeBooks');
    }
}
