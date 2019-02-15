<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayloadColumnToOneTimeTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('one_time_tokens', function (Blueprint $table) {
            $table->mediumText('payload')->nullable()->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('one_time_tokens', function (Blueprint $table) {
            $table->dropColumn('payload');
        });
    }
}
