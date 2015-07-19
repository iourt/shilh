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
            $table->tinyInteger('type');//shilehui.notification_type
            //$table->string('asso_type');//  Polymorphic Eloquent relationships
            $table->integer('asso_id');//article_comments.id/article_praises.id/article_collections.id
                                       //notices.id/chats.id
            $table->tinyInteger('has_read');
            $table->text('payload');
            $table->integer('sender_id');
            //$table->text('content');
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
