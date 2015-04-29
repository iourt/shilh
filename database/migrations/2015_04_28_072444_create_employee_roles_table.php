<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('employee_roles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('employee_id');
			$table->integer('role_id');
			$table->timestamps();
            $table->primary(['employee_id', 'role_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('employee_roles');
	}

}
