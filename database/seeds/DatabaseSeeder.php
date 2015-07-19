<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
        if (App::environment() === 'production') {
            $this->call('ProductionSeeder');
        } else {
            $this->call('StagingSeeder');
        }
	}

}

