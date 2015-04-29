<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRelationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_relations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id');
			$table->integer('buddy_user_id');
			$table->enum('relation', ['fan','follow'])->default('fan');//buddy_user is user's fan/follow
			$table->timestamps();
            $table->primary(['user_id', 'buddy_user_id','relation']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_relations');
	}

}
