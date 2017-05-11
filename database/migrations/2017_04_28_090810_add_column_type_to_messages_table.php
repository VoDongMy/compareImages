<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTypeToMessagesTable extends Migration
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
                    DB::statement("ALTER TABLE `messages` ADD `type` INT(10) DEFAULT 0 AFTER content");
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
