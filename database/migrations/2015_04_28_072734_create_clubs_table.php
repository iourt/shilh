<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClubsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clubs', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->text('brief');
            $table->integer('to_category_id')->default(0);//this article is also post to the category
            $table->integer('cover_image_id')->default(0);
            $table->char('letter')->default('');
            $table->integer('user_num')->default(0);
            $table->integer('article_num')->default(0);
            $table->datetime('article_updated_at');
            $table->integer('acvitity_id')->default(0);
            $table->integer('today_article_num')->default(0);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('clubs');
	}

}
