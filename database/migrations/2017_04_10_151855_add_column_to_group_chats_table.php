<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToGroupChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('group_chats'))
        {
            Schema::table('group_chats', function (Blueprint $table)
            {
                DB::statement("ALTER TABLE `group_chats` ADD `type` INT(10) DEFAULT 0 AFTER descript");
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
