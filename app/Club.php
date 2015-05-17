<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Club extends Model {

    protected $dates = ['article_updated_at'];

    public function getCoverImageUrlAttribute($value) {
        return sprintf("coverimages/%s/%s.jpg", $this->id, $this->cover_image_id);
    }
}
