<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCategorySubscriptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_category_subscriptions', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->integer('category_id');
			$table->timestamps();
            $table->unique(['user_id', 'category_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_category_subscriptions');
	}

}
