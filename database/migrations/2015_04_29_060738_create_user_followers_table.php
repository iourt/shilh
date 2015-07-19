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
			$table->integer('user_id');
			$table->integer('follower_id');//关注user_id的用户对应id, 即follower_id是user_id的粉丝
			$table->tinyinteger('is_twoway');
			$table->timestamps();
            $table->unique(['user_id', 'follower_id']);
            $table->index('follower_id');
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
