<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model {

    protected $guarded = [];
    public function cover_image(){
        return $this->belongsTo('\App\CoverImage');
    }
    public function club(){
        return $this->belongsTo('\App\Club');
    }
}
