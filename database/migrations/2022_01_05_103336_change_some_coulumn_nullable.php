<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSomeCoulumnNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('storesAddress', function (Blueprint $table) {
            //
            $table->string('city')->nullable()->change();
            $table->string('province')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('storesAddress', function (Blueprint $table) {
            //
            $table->dropColumn('city');
            $table->dropColumn('province');
        });
    }
}
