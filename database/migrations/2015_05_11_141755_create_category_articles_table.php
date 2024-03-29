<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryArticlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('category_articles', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('category_id');
            $table->integer('article_id');
			$table->timestamps();
            $table->unique(['category_id', 'article_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('category_articles');
	}

}
