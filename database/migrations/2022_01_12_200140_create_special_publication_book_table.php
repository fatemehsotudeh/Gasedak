<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialPublicationBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specialPublicationBooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('specialPublicationId');
            $table->unsignedBigInteger('bookId');
            $table->timestamps();

            $table->foreign('specialPublicationId')
                ->references('id')
                ->on('stores');

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
        Schema::dropIfExists('specialPublicationBooks');
    }
}
