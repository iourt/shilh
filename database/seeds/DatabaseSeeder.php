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


