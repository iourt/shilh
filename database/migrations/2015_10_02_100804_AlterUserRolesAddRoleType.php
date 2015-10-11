<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserRolesAddRoleType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('user_roles', function($table) {
            $table->integer('user_id')->unique();
            $table->integer('role_type');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('user_roles', function($table) {
            $table->dropColumn('user_id');
            $table->dropColumn('role_type');
        });
	}

}
