<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('depositTransactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->bigInteger('amount');
            $table->string('authority');
            $table->string('refId')->nullable();
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
        Schema::dropIfExists('depositTransactions');
    }
}
