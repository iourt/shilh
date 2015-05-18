<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ClubTopUser extends Model {

	//
    public function user(){
        return $this->belongsTo('\App\User');
    }

}
