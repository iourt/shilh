<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class LargeDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		$this->call('ConfigSeriesTableSeeder');
		$this->call('UserSeriesTableSeeder');
		$this->call('ArticleSeriesTableSeeder');
	}

}
