<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserClubAttendancesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_club_attendances', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('user_id');
            $table->integer('club_id');
            $table->date('attended_at');
            $table->integer('days');
			$table->timestamps();
            $table->unique(['user_id','club_id','attended_at']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_club_attendances');
	}

}
