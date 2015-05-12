<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRecentUpdatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_recent_updates', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->tinyinteger('type');
            $table->integer('type_id');
            $table->integer('article_id');
			$table->timestamps();
            $table->unique(['user_id', 'type', 'type_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_recent_updates');
	}

}
