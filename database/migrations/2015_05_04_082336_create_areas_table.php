<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\Model;

class CreateAreasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('areas', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name', 32);
            $table->tinyinteger('level');
            $table->integer('parent_id');
			$table->timestamps();
		});
        Model::unguard();
        \App\Area::create(['id'=>1, 'name'=>'北京', 'level' => Config::get('shilehui.area_level')['province'], 'parent_id' => 0]); 
        \App\Area::create(['id'=>2, 'name'=>'东城区', 'level' => Config::get('shilehui.area_level')['city'], 'parent_id'=>1]); 
        \App\Area::create(['id'=>3, 'name'=>'海淀区', 'level' => Config::get('shilehui.area_level')['city'], 'parent_id' => 1]); 

        \App\Area::create(['id'=>4, 'name'=>'浙江', 'level' => Config::get('shilehui.area_level')['province'], 'parent_id' => 0]); 
        \App\Area::create(['id'=>5, 'name'=>'杭州市', 'level' => Config::get('shilehui.area_level')['city'], 'parent_id'=>4]); 
        \App\Area::create(['id'=>6, 'name'=>'江干区', 'level' => Config::get('shilehui.area_level')['county'], 'parent_id' => 5]); 
        \App\Area::create(['id'=>7, 'name'=>'建德县', 'level' => Config::get('shilehui.area_level')['county'], 'parent_id' => 6]); 
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('areas');
	}

}
