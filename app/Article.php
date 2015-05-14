<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model {
    public function  images(){
       return $this->hasMany('App\ArticleImage'); 
    }
    public function comments(){
        return $this->hasMany('App\ArticleComment');
    }
    public function user(){
        return $this->belongTo('App\User');
    }
}
