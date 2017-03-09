<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fb_id')->unique();
            $table->string('email')->unique();
            $table->string('name');
            $table->string('profile_image');
            $table->string('curr_lat');
            $table->string('curr_long');
            $table->integer('gender');
            $table->date('dob');
            $table->string('location');
            $table->string('phone');
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
