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
    protected $guarded = [];
    public function avatars() {
        return $this->hasMany('App\UserAvatar');
    }
    public function avatar() {
        return $this->hasOne('App\UserAvatar','id','user_avatar_id');
    }
    public function role() {
        return $this->hasOne('\App\UserRole');
    }
    public function limited_article() {
        return $this->hasMany('\App\Article')->limit(500);
    }

    
}
