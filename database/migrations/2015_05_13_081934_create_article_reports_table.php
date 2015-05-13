<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleReportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('article_reports', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('article_id');
            $table->integer('user_id');
            $table->text('reason');
            $table->text('contact');
			$table->timestamps();
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
		Schema::drop('article_reports');
	}

}
