<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticketMessages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticketId');
            $table->unsignedBigInteger('senderId');
            $table->boolean('isAdmin')->default(0);//0 means user , 1 means admin
            $table->string('message')->nullable();
            $table->string('filePath')->nullable();
            $table->timestamps();

            $table->foreign('ticketId')
                ->references('id')
                ->on('tickets');

            $table->foreign('senderId')
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
        Schema::dropIfExists('ticketMessages');
    }
}
