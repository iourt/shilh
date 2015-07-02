<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class VerifyCode extends Model {


    protected $dates   = ['expired_at'];
    protected $guarded = ['id'];

    public function getIsExpired(){
        return mktime($this->expired_at) < time();
    }
}
