<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->boolean('disabled')->default(false);
            $table->string('firstname',55)->nullable();
            $table->string('lastname',55)->nullable();
            $table->string('gender')->default('مرد');
            $table->date('birthdate')->nullable();
            $table->string('email')->nullable();
            $table->boolean('isAdmin')->default(false);
            $table->string('password');
            //faviriotes
            $table->string('phoneNumber',20)->unique();
            $table->integer('credit')->default(0);
            //history
            $table->string('province')->default('فارس');
            $table->string('city')->default('شیراز');
            //purchasedAudios
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
        Schema::dropIfExists('users');
    }
}
