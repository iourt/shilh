<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model {

    protected $guarded = [];
    public function getUrlAttribute($value) {
        return sprintf("articleimages/%s/%s.%s", $this->article_id, $this->id, $this->ext);
    }
    public function getStorageFileAttribute($value) {
        return sprintf("%s/%s.%s", \App\Lib\Image::getPathOfName($this->filename), $this->filename, $this->ext); 
    }

}
