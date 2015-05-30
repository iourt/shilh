<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAvatarsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_avatars', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->string('filename');
            $table->string('origname');
            $table->string('ext');
            $table->datetime('use_time');
			$table->timestamps();
            $table->index('user_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_avatars');
	}

}
