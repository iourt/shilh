<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSubjectAddClubCategory extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('subjects', function($table) {
            $table->string('club_id');
            $table->string('category_id');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('subjects', function($table) {
            $table->dropColumn('club_id');
            $table->dropColumn('category_id');
        });
	}

}
