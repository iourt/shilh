<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('article_comments', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('article_id');
            $table->integer('user_id');
            $table->string('user_name');
            $table->text('comment');
            $table->datetime('post_at');
            $table->tinyinteger('is_check');
			$table->timestamps();
            $table->index('article_id');
            $table->index('user_id');
            $table->index('is_check');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('article_comments');
	}

}
