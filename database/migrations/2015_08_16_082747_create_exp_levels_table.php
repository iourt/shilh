<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpLevelsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('exp_levels', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('level');
            $table->string('name');
            $table->integer('exp');
            $table->integer('cover_image_id');
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
		Schema::drop('exp_levels');
	}

}
