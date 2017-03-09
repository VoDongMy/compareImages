<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('bid_id')->unsigned();
            $table->integer('from_user_id')->unsigned();
            $table->string('message');
            $table->integer('is_read');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('from_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('bid_id')
                ->references('id')
                ->on('bids')
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
        Schema::drop('notifications');
    }
}
