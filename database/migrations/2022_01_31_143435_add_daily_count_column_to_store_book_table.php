<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDailyCountColumnToStoreBookTable extends Migration
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
            $table->integer('dailyCount')->nullable()->after('isDailyDiscount');
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
            $table->dropColumn('dailyCount');
        });
    }
}
