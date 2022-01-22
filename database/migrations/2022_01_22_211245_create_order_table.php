<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('userAddressId')->nullable();
            $table->unsignedBigInteger('storeId');
            $table->unsignedBigInteger('totalPrice')->default(0);
            $table->unsignedBigInteger('totalDiscountAmount')->default(0);
            $table->unsignedBigInteger('totalQuantity')->default(0);
            $table->unsignedBigInteger('totalWeight')->default(0);
            $table->unsignedBigInteger('storeCost')->default(0);
            $table->unsignedBigInteger('ghasedakCost')->default(0);
            $table->unsignedBigInteger('orderStatusId');
            $table->unsignedBigInteger('shipperId')->nullable();
            $table->string('tapinBarcode')->nullable();
            $table->string('trackingCode');
            $table->boolean('isAccepted')->nullable();
            $table->boolean('adminReject')->default(false);
            $table->boolean('partial')->default(false);
            $table->string('paymentType')->nullable();
            $table->unsignedBigInteger('paymentId')->nullable();
            $table->boolean('isPaid')->default(false);
            $table->timestamp('orderDate')->nullable();
            $table->timestamp('sentDate')->nullable();
            $table->timestamp('deliveredDate')->nullable();
            $table->timestamp('rejectDate')->nullable();
            $table->timestamps();

            $table->foreign('userId')
                ->references('id')
                ->on('users');

            $table->foreign('userAddressId')
                ->references('id')
                ->on('usersAddress');

            $table->foreign('storeId')
                ->references('id')
                ->on('stores');

            $table->foreign('orderStatusId')
                ->references('id')
                ->on('ordersStatus');

            $table->foreign('shipperId')
                ->references('id')
                ->on('shippers');

            $table->foreign('paymentId')
                ->references('id')
                ->on('depositTransactions');

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
        Schema::dropIfExists('orders');
    }
}
