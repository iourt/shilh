<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerifyCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('verify_codes', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('phone', 15);
            $table->integer('type');
            $table->string('code', 15);
			$table->timestamps();
            $table->unique(['phone','code']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('verify_codes');
	}

}
