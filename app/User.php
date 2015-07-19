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
    public function avatars() {
        return $this->hasMany('App\UserAvatar');
    }
    public function avatar() {
        return $this->hasOne('App\UserAvatar');
    }
    public function user_roles() {
        return $this->hasMany('\App\UserRole');
    }

    
}
