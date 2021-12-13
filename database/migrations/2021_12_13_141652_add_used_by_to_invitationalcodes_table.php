<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsedByToInvitationalcodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invitationalcodes', function (Blueprint $table) {
            //
            $table->json('usedBy')->nullable()->after('invitationUsed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invitationalcodes', function (Blueprint $table) {
            //
            $table->dropColumn('usedBy');
        });
    }
}
