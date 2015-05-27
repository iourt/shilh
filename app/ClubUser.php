<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ClubUser extends Model {

    protected $guarded = ['id'];
    public function club(){
        return $this->belongsTo('\App\Club');
    }
    public function user(){
        return $this->belongsTo('\App\User');
    }
}
