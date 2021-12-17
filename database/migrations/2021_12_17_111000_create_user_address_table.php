<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usersAddress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('lat');
            $table->string('lng');
            $table->string('province');
            $table->string('city');
            $table->string('postalCode',10);
            $table->string('postalAddress');
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
        Schema::dropIfExists('usersAddress');
    }
}
