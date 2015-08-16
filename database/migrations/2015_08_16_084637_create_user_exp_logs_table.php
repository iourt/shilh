<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserExpLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_exp_logs', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('uniq_id');
            $table->integer('user_id');
            $table->integer('exp');
            $table->integer('action');
            $table->string('data');
			$table->timestamps();
            $table->index('user_id');
            $table->unique('uniq_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_exp_logs');
	}

}
