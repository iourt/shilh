<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClubTopUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('club_top_users', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('club_id');
            $table->integer('user_id');
            $table->integer('article_num');
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
		Schema::drop('club_top_users');
	}

}
