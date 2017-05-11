<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDeviceTypeToUserTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_tokens'))
        {
            Schema::table('user_tokens', function (Blueprint $table)
            {
                DB::statement('ALTER TABLE `user_tokens` ADD COLUMN `device_type` VARCHAR(10) NULL AFTER `key`;');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'udid')) {
                    $table->dropColumn('udid');
                }
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
        
    }
}
