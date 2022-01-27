<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discountCodes', function (Blueprint $table) {
            $table->id();
            $table->string('userType')->default('all');
            $table->unsignedBigInteger('userId')->nullable();
            $table->string('discountType');
            $table->string('issuedBy')->default('ghasedak');
            $table->string('discountRef');
            $table->string('storeRef')->default('all');
            $table->json('storesId')->nullable();
            $table->bigInteger('amount');
            $table->bigInteger('upperBound');
            $table->bigInteger('lowerBound');
            $table->unsignedBigInteger('bookId')->nullable();
            $table->json('ordersId')->nullable();
            $table->string('code');
            $table->timestamp('expDate');
            $table->boolean('isUsed')->default(false);
            $table->boolean('public')->default(false);
            $table->bigInteger('usedCount')->default(0);
            $table->bigInteger('useLimit')->default(1);
            $table->timestamps();

            $table->foreign('userId')
                ->references('id')
                ->on('users');

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
        Schema::dropIfExists('discountCodes');
    }
}
