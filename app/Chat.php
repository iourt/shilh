<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model {

    public function little_user(){
        return $this->belongsTo('\App\User', 'little_user_id');
    }
    public function great_user(){
        return $this->belongsTo('\App\User', 'great_user_id');
    }
    public function messages(){
        return $this->hasMany('\App\ChatMessage');
    }
}
