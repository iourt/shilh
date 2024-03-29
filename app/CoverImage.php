<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CoverImage extends Model {
    protected $guarded = ['id'];

    public function getUrlAttribute($value) {
        return sprintf("coverimages/%s.%s", $this->id, $this->ext);
    }
    public function getStorageFileAttribute($value) {
        return sprintf("%s/%s.%s", \App\Lib\Image::getPathOfName($this->filename), $this->filename, $this->ext); 
    }

}
