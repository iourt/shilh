<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('articles', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->string('title');
            $table->integer('category_id');
            $table->integer('club_id');
            $table->integer('activity_id');
            $table->integer('subject_id');
            $table->integer('view_num');
            $table->integer('comment_num');
            $table->integer('praise_num');
            $table->integer('collection_num');
            $table->datetime('user_updated_at');
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
		Schema::drop('articles');
	}

}
