<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model {

    public function getUrlAttribute($value) {
        return sprintf("bannerimages/%s.%s", $this->id, $this->ext);
    }
    public function getStorageFileAttribute($value) {
        return sprintf("%s/%s.%s", \App\Lib\Image::getPathOfName($this->filename), $this->filename, $this->ext); 
    }

}
