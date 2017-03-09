<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExchangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchanges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_toexchange')->unsigned();
            $table->integer('item_exchange')->unsigned();
            $table->integer('from_user')->unsigned();
            $table->integer('to_user')->unsigned();
            $table->integer('status');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('from_user')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('item_toexchange')
                ->references('id')
                ->on('items')
                ->onDelete('cascade');

            $table->foreign('to_user')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('item_exchange')
                ->references('id')
                ->on('items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('exchanges');
    }
}
