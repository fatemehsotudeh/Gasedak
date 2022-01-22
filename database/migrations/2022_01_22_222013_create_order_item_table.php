<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderItems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orderId');
            $table->unsignedBigInteger('bookId');
            $table->unsignedBigInteger('price')->default(0);
            $table->integer('discountAmount')->default(0);
            $table->unsignedBigInteger('quantity')->default(0);
            $table->unsignedBigInteger('acceptedQuantity')->nullable();
            $table->integer('weight')->nullable();
            $table->timestamps();

            $table->foreign('orderId')
                ->references('id')
                ->on('orders');

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
        Schema::dropIfExists('orderItems');
    }
}
