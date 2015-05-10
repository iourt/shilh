<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleImagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('article_images', function(Blueprint $table)
		{
            $table->increments('id');
            $table->integer('article_id');
            $table->text('brief');
            $table->string('filename');
            $table->string('ext');
            $table->integer('size');
            $table->integer('width');
            $table->integer('height');
            $table->integer('thumb_width');
            $table->integer('thumb_height');
            $table->tinyinteger('is_check');
			$table->timestamps();
            $table->index('article_id');
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
		Schema::drop('article_images');
	}

}
