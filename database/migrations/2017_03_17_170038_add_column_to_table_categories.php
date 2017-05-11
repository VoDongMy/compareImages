<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToTableCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('categories'))
        {
            Schema::table('categories', function (Blueprint $table)
            {
                DB::statement("ALTER TABLE `categories` ADD `user_id`  INT(8) NULL DEFAULT 0 AFTER `name` ;");
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
