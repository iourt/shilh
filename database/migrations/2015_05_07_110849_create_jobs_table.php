<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;

class CreateJobsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jobs', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->integer('seq_id');
			$table->timestamps();
		});
        Model::unguard();
        \App\Job::create(['id'=>1, 'seq_id'=>1, 'name'=>'教师']); 
        \App\Job::create(['id'=>2, 'seq_id'=>2, 'name'=>'校长']); 
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('jobs');
	}

}
