<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClubArticlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('club_articles', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('club_id')->default(0);
            $table->integer('article_id')->default(0);
			$table->timestamps();
            $table->unique(['club_id', 'article_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('club_articles');
	}

}
