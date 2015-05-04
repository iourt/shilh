<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMobileVerifyCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('mobile_verify_codes', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('mobile');
            $table->string('code');
            $table->datetime('expired_at');
            $table->unique('mobile');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('mobile_verify_codes');
	}

}
