<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUserIdColumnInOneTimeTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('one_time_tokens', function (Blueprint $table) {
            $table->string('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('one_time_tokens', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->change();
        });
    }
}
