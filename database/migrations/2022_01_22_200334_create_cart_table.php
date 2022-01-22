<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('storeId');
            $table->unsignedBigInteger('totalPrice')->default(0);
            $table->unsignedBigInteger('totalDiscountAmount')->default(0);
            $table->unsignedBigInteger('totalQuantity')->default(0);
            $table->timestamps();

            $table->foreign('userId')
                ->references('id')
                ->on('users');

            $table->foreign('storeId')
                ->references('id')
                ->on('stores');

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
        Schema::dropIfExists('carts');
    }
}
