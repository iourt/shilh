<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notifications', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->tinyInteger('type');
            $table->integer('asso_id');//article_comments.id/article_praises.id/article_collections.id
                                       //notices.id/chats.id
            $table->tinyInteger('has_read');
            $table->text('payload');
			$table->timestamps();
            $table->unique(['user_id', 'type', 'asso_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('notifications');
	}

}
