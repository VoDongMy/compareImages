<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToTablePictures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('pictures'))
        {
            Schema::table('pictures', function (Blueprint $table)
            {
                DB::statement('ALTER TABLE `pictures` ADD COLUMN `user_id` VARCHAR(45) NULL AFTER `id`;');
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
