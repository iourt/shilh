<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNotifictionChangeUKey extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('notifications', function($table) {
            $table->dropUnique('notifications_user_id_type_asso_id_unique');
            $table->index(['user_id', 'type', 'asso_id']);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('notifications', function($table) {
            $table->dropIndex('notifications_user_id_type_asso_id_index');
            $table->unique(['user_id', 'type', 'asso_id']);
        });
	}

}
