<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class VerifyCode extends Model {


    protected $guarded = ['id'];

    public function getIsExpired(){
        $seconds = 0;
        foreach(config('shilehui.verify_code') as $v){
            if($v->type == $this->type) $seconds = $v->seconds;
        }
        return mktime($this->updated_at) + $seconds < time();
    }

}
