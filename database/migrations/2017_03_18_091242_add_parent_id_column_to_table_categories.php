<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdColumnToTableCategories extends Migration
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
                DB::statement("ALTER TABLE `categories` ADD `parent_id`  INT(8) NULL DEFAULT 0 AFTER `user_id`, ADD `position`  INT(8) NULL DEFAULT 0 AFTER `parent_id` ;");
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
