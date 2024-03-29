<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFollower extends Model {
    protected $guarded = ['id'];
    public function user(){
        return $this->belongsTo('\App\User');
    }
    public function follower(){
        return $this->belongsTo('\App\user', 'follower_id');
    }

}
