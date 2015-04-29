<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('mobile', 16);
            $table->string('encrypt_pass', 32);
            $table->integer('challenge_id')->default(0);
            $table->string('name', 32);
            $table->enum('sex', ['male', 'female', ''])->default('');;
            $table->integer('job_id')->default(0);
            $table->integer('area_id')->default(0);
            $table->integer('user_image_id')->default(0);
            $table->integer('exp_num')->default(0);
            $table->integer('follow_numm')->default(0);
            $table->integer('fans_numm')->default(0);
            $table->integer('collect_numm')->default(0);
            $table->integer('club_numm')->default(0);
            $table->integer('article_numm')->default(0);
            $table->tinyInteger('push_state')->default(0);
            $table->tinyInteger('phone_state')->default(0);
            $table->tinyInteger('whisper_state')->default(0);
            $table->tinyInteger('photo_state')->default(0);
			$table->timestamps();
            $table->unique('mobile');
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
