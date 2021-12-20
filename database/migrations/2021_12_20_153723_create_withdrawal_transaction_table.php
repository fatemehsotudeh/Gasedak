<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawalTransactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->bigInteger('amount');
            $table->string('bankId');
            $table->string('transactionStatus');
            $table->timestamps();

            $table->foreign('userId')
                ->references('id')
                ->on('users');

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
        Schema::dropIfExists('withdrawalTransactions');
    }
}
