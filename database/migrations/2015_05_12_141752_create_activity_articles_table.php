<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityArticlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('activity_articles', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('activity_id');
            $table->integer('article_id');
			$table->timestamps();
            $table->unique(['acvivity_id', 'article_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('activity_articles');
	}

}
