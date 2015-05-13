<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleCollectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('article_collections', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('article_id');
            $table->integer('user_id');
			$table->timestamps();
            $table->unique(['user_id', 'article_id']);
            $table->index('article_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('article_collections');
	}

}
