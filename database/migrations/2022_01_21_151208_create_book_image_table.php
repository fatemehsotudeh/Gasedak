<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booksImage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bookId');
            $table->string('imagePath')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('booksImage');
    }
}
