<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAvatar extends Model {

    public function getUrlAttribute($value) {
        return sprintf("useravatars/%s/%s.%s", $this->user_id, $this->id, $this->ext);
    }
    public function getStorageFileAttribute($value) {
        return sprintf("%s/%s.%s", \App\Lib\Image::getPathOfName($this->filename), $this->filename, $this->ext); 
    }

}
