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

		$this->call('UserImageTableSeeder');
		$this->call('UserTableSeeder');
		$this->call('CategorySeeder');
		$this->call('JobSeeder');
		$this->call('AreaSeeder');
	}

}

class UserTableSeeder extends Seeder {

    public function run()
    {
        DB::table('users')->delete();
        for($i=1;$i<5;$i++){
            $salt = rand(10000000,99999999);
            App\User::create(['id'=>$i, 'mobile' => '1367777555'.$i, 'encrypt_pass' => md5($salt.'\t111111'), 'salt'=>$salt, 'user_image_id' => $i]);
        }
    }
}

class UserImageTableSeeder extends Seeder {
    public function run() {
        DB::table('user_images')->delete();
        for($i=1;$i<8;$i++){
            $user_id = $i%5; 
            App\UserImage::create(['id' => $i, 'user_id' => $user_id, 'name' => date('YmdHis')."_".rand(1000,9999)."_".$user_id.".jpg"]);
        }
    }
}

class CategorySeeder extends Seeder {
    public function run() {
        DB::table('categories')->delete();
        for($i=1;$i<8;$i++){
            $level = $i%3 + 1;
            $parent_id = $level == 1 ? 0 : $i-1; 
            App\Category::create(['id' => $i, 'level' => $i%3+1, 'name' => "R-$i-$level", 'parent_id' => $parent_id]);
        }
    }
    
}
class JobSeeder extends Seeder {
    public function run() {
        DB::table('jobs')->delete();
        App\Job::create(['id'=>1, 'seq_id'=>1, 'name'=>'教师']); 
        App\Job::create(['id'=>2, 'seq_id'=>2, 'name'=>'校长']); 
    }
}
class AreaSeeder extends Seeder {
    public function run() {
        DB::table('areas')->delete();
        App\Area::create(['id'=>1, 'name'=>'北京', 'level' => Config::get('shilehui.area_level')['province'], 'parent_id' => 0]); 
        App\Area::create(['id'=>2, 'name'=>'东城区', 'level' => Config::get('shilehui.area_level')['city'], 'parent_id'=>1]); 
        App\Area::create(['id'=>3, 'name'=>'海淀区', 'level' => Config::get('shilehui.area_level')['city'], 'parent_id' => 1]); 

        App\Area::create(['id'=>4, 'name'=>'浙江', 'level' => Config::get('shilehui.area_level')['province'], 'parent_id' => 0]); 
        App\Area::create(['id'=>5, 'name'=>'杭州市', 'level' => Config::get('shilehui.area_level')['city'], 'parent_id'=>4]); 
        App\Area::create(['id'=>6, 'name'=>'江干区', 'level' => Config::get('shilehui.area_level')['county'], 'parent_id' => 5]); 
        App\Area::create(['id'=>7, 'name'=>'建德县', 'level' => Config::get('shilehui.area_level')['county'], 'parent_id' => 6]); 
    }
}

