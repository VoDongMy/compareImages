<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToTableItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        if (Schema::hasTable('items'))
        {

            Schema::table('items', function (Blueprint $table)
            {
                DB::statement("ALTER TABLE `items` ADD `status` INT(11) NULL DEFAULT NULL AFTER is_exchange");
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
