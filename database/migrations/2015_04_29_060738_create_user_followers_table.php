<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFollowersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_followers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user1_id');
			$table->integer('user2_id');
			$table->tinyinteger('relation');// 1: user1 is follower; 2: user2 is follower; 3: user1 and user2 are other's follower
			$table->timestamps();
            $table->unique(['user1_id', 'user2_id']);
            $table->index('user2_id');
            $table->index(
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_followers');
	}

}
