<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnTableMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('messages'))
        {
            Schema::table('messages', function (Blueprint $table)
            {
                DB::statement("ALTER TABLE `messages` ADD COLUMN `group_chat_id` INT(10) DEFAULT 0 AFTER `user_id`;");
            });
        }
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
