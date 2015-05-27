<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
	protected $table = 'users';
	protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'push_state'    => 'boolean',
        'whisper_state' => 'boolean',
        'phone_state'   => 'boolean',
        'photo_state'   => 'boolean',
    ];
    public function user_image() {
        return $this->hasMany('App\UserImage', 'id','user_image_id');
    }
    public function getUserImageUrlAttribute($value) {
        return sprintf("userimages/%s/%s.jpg", $this->id, $this->user_image_id);
    }

    
}
