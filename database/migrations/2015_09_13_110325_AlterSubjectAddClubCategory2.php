<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSubjectAddClubCategory2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('subjects', function($table) {
            $table->integer('club_id')->change();
            $table->integer('category_id')->change();
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
            $table->string('club_id')->change();
            $table->string('category_id')->change();
        });
	}

}
