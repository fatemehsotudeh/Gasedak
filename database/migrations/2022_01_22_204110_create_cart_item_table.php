<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cartItems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cartId');
            $table->unsignedBigInteger('bookId');
            $table->unsignedBigInteger('price')->default(0);
            $table->integer('discountAmount')->default(0);
            $table->unsignedBigInteger('quantity')->default(1);
            $table->integer('weight')->nullable();
            $table->timestamps();

            $table->foreign('cartId')
                ->references('id')
                ->on('carts');

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
        Schema::dropIfExists('cartItems');
    }
}
