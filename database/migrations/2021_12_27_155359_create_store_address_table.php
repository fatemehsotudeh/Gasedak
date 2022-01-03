<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storesAddress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('storeId');
            $table->string('city');
            $table->string('province');
            $table->string('postalCode',10)->nullable();
            $table->string('postalAddress')->nullable();
            $table->string('postalArea')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->timestamps();

            $table->foreign('storeId')
                ->references('id')
                ->on('stores');

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
        Schema::dropIfExists('storesAddress');
    }
}
