<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticlePraise extends Model {

    protected $guarded = [];
    public function user(){
        return $this->belongsTo('\App\User');
    }
    public function article(){
        return $this->belongsTo('\App\Article');
    }

}
