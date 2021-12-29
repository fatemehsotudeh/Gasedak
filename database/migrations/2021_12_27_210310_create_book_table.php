<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('inventory');
            $table->integer('price');
            $table->integer('discountAmount')->default(0);
            $table->integer('edition')->nullable();
            $table->integer('publishedYear')->nullable();
            $table->string('ISBN')->unique();
            $table->string('size')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('pageCount')->nullable();
            $table->string('pageType')->nullable();
            $table->string('coverType')->nullable();
            $table->integer('volumeCount')->nullable();
            $table->string('publisher')->nullable();
            $table->json('authors');
            $table->json('translators');
            $table->string('imagePath')->nullable();
            $table->unsignedBigInteger('categoryId')->nullable();
            $table->unsignedBigInteger('childCategoryId')->nullable();
            $table->string('bookType')->default('textBook');
            $table->string('ageCategory')->default('allAges');
            $table->decimal('rate',2,1)->nullable();
            $table->string('attachment')->nullable();
            $table->text('description')->nullable();
            $table->boolean('isCollection')->default(false);
            $table->bigInteger('purchaseCount')->default(0);
            $table->bigInteger('commentCount')->default(0);
            $table->bigInteger('viewCount')->default(0);
            $table->biginteger('favoriteCount')->default(0);
            $table->boolean('hasAudio')->default(false);
            $table->timestamps();

            $table->foreign('categoryId')
                ->references('id')
                ->on('categories');

            $table->foreign('childCategoryId')
                ->references('id')
                ->on('childCategories');

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
        Schema::dropIfExists('books');
    }
}
