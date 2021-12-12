<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smsTokens', function (Blueprint $table) {
            $table->id();
            $table->string('phoneNumber',20)->unique();
            $table->string('smsCode');
            $table->boolean('isVerified')->default(false);
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
        Schema::dropIfExists('smsTokens');
    }
}
