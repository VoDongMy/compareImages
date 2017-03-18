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
                DB::statement('ALTER TABLE `pictures` 
                                ADD COLUMN `thumbnail` VARCHAR(45) NULL AFTER `url`,
                                ADD COLUMN `file_name` VARCHAR(45) NULL AFTER `thumbnail`,
                                ADD COLUMN `size` VARCHAR(45) NULL AFTER `file_name`;');
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
