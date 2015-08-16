<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpLevel extends Model {

    public function icon(){
        return $this->belongsTo('\App\CoverImage');
    }
    
}
