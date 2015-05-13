<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_comments', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('activity_id');
            $table->integer('user_id');
            $table->integer('comment');
			$table->timestamps();
            $table->index('activity_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity_comments');
	}

}
