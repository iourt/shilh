<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model {

    protected $guarded = [];
    public function cover_image(){
        return $this->belongsTo('\App\CoverImage');
    }

}
