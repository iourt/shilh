<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubjectArticlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subject_articles', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('subject_id');
            $table->integer('article_id');
			$table->timestamps();
            $table->unique(['subject_id', 'article_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subject_articles');
	}

}
