<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('bookId');
            $table->unsignedBigInteger('parentId')->nullable();
            $table->text('message')->nullable();
            $table->decimal('rate',2,1)->nullable();
            $table->boolean('isApproved')->default(false);
            $table->timestamps();

            $table->foreign('userId')
                ->references('id')
                ->on('users');

            $table->foreign('bookId')
                ->references('id')
                ->on('books');

            $table->foreign('parentId')
                ->references('id')
                ->on('comments');

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
        Schema::dropIfExists('comments');
    }
}
