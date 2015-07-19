<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model {

    protected $guarded = [];
    public function cover_image(){
        return $this->belongsTo('\App\CoverImage');
    }
}
