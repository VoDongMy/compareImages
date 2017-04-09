<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('messages')) {
            Schema::drop('messages');
        }

        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('content');
            $table->integer('is_read');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
