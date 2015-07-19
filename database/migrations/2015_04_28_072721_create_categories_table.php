<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('categories', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name', 32);
            $table->tinyInteger('level')->default(1);
            $table->tinyInteger('is_leaf')->default(1);
            $table->integer('cover_image_id')->default(0);
            $table->integer('parent_id')->default(0);
            $table->integer('article_num')->default(0);
            $table->integer('follow_num')->default(0);
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
		Schema::drop('categories');
	}

}
