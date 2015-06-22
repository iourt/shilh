<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Club extends Model {

    protected $dates = ['article_updated_at'];

    public function cover_image(){
        return $this->belongsTo('\App\CoverImage');
    }

    public function activity(){
        return $this->belongsTo('\App\Activity');
    }

}
