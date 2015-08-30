<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {

    protected $guarded = [];
    public function cover_image(){
        return $this->belongsTo('\App\CoverImage');
    }
    public function clubs(){
        return $this->belongsTo('\App\Club', 'id', 'to_category_id');
    }
}
