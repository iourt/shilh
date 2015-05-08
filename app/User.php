<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
	protected $table = 'users';
	protected $hidden = ['password', 'remember_token'];
    public function user_image() {
        return $this->hasMany('App\UserImage', 'id','user_image_id');
    }
    public function getUserImageFileAttribute($value) {
        if(empty($this->user_image)){
            $filename = config('shilehui.default_user_image');
        } else {
            $filename = $this->user_image->name;
        }
        return \App\Lib\Image::getFileByName($filename); 
    }

    
}
