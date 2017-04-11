<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnTableGroupChats extends Migration
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
            Schema::table('group_chats', function (Blueprint $table)
            {
                DB::statement("ALTER TABLE `group_chats` 
                                CHANGE COLUMN `type` `object_type` INT(10) NULL DEFAULT 0 ,
                                ADD COLUMN `object_id` INT(10) NULL DEFAULT 0 AFTER `object_type`");
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
