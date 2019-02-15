<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOneTimeTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('one_time_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->string('operation')->index();
            $table->string('identifier')->index();
            $table->unsignedInteger('code')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('used_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('one_time_tokens');
    }
}
