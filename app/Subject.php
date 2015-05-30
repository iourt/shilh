<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model {

    public function cover_image(){
        return $this->belongsTo('\App\CoverImage');
    }
}
