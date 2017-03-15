<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSettingPriceLowHigh extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('settings'))
        {

            Schema::table('settings', function (Blueprint $table)
            {
                if (!Schema::hasColumn('settings', 'price'))
                {
                    $table->dropColumn('price');
                }
                DB::statement("ALTER TABLE `settings` ADD `low_price` VARCHAR(20) NULL DEFAULT NULL AFTER distance, ADD `high_price` VARCHAR(20) NULL DEFAULT NULL AFTER low_price");
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
