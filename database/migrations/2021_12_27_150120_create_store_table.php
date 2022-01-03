<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('isVerified')->default(false);
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->string('username',55)->nullable();
            $table->string('IBAN',24)->nullable();
            $table->string('mobileNumber')->unique()->nullable();
            $table->string('kind',55)->nullable();
            $table->json('sellers')->nullable();
            $table->string('fixedPhoneNumber')->nullable();
            $table->boolean('isOpen')->default(true);
            $table->string('storeType')->default('bookShop');
            $table->boolean('ordersActive')->default(true);
            //$table->string('tapinId')->nullable();
            $table->integer('ghasedakShare')->default(5);
            $table->boolean('isSuspended')->default(false);
            $table->string('logoPath')->nullable();
            $table->text('description')->nullable();
            $table->text('hashtags')->nullable();
            $table->string('imagePath')->nullable();
            $table->boolean('exhibition')->default(false);
            $table->boolean('Library')->default(false);
            $table->string('releaseType',55)->nullable();
            $table->decimal('rate',2,1)->nullable();
            $table->bigInteger('viewCount')->default(0);
            $table->biginteger('favoriteCount')->default(0);
            $table->bigInteger('purchaseCount')->default(0);
            $table->timestamps();

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
        Schema::dropIfExists('stores');
    }
}
