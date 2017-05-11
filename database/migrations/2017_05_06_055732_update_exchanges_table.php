<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('exchanges')) {
            Schema::drop('exchanges');
        }

        Schema::create('exchanges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_id');
            $table->integer('item_exchange_id');
            $table->integer('user_id');
            $table->integer('status');
            $table->softDeletes();
            $table->timestamps();
        });
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
