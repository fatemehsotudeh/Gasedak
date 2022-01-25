<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropStoreBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('storeBooks', function (Blueprint $table) {
            //
            Schema::dropIfExists('storeBooks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('storeBooks', function (Blueprint $table) {
            //
            $table->id();
            $table->unsignedBigInteger('storeId');
            $table->unsignedBigInteger('bookId');
            $table->timestamps();

            $table->foreign('storeId')
                ->references('id')
                ->on('stores');

            $table->foreign('bookId')
                ->references('id')
                ->on('books');
        });
    }
}
