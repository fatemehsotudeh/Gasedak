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
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->string('mobileNumber',20)->unique();
            $table->json('sellers')->nullable();
            $table->string('fixedPhoneNumber')->nullable();
            $table->boolean('isOpen')->default(true);
            $table->string('storeType')->default('bookShop');
            $table->boolean('ordersActive')->default(true);
            $table->string('tapinId')->nullable();
            $table->integer('ghasedakShare')->default(5);
            $table->boolean('isSuspended')->default(false);
            $table->string('logoPath')->nullable();
            $table->text('description')->nullable();
            $table->string('imagePath')->nullable();
            $table->boolean('exhibition');
            $table->boolean('isLibrary');
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
