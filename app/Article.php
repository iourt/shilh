<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model {
    protected $dates = ['user_updated_at'];

    public function  images(){
       return $this->hasMany('App\ArticleImage'); 
    }
    public function comments(){
        return $this->hasMany('App\ArticleComment');
    }
    public function user(){
        return $this->belongsTo('App\User');
    }
    public function club(){
        return $this->belongsTo('App\Club');
    }
    public function activity(){
        return $this->belongsTo('App\Activity');
    }
    public function subject(){
        return $this->belongsTo('App\Subject');
    }
    public function getIsShownInCategoryAttribute($value){
        return \App\CategoryArticle::where('category_id', $this->category_id)->where('article_id', $this->id)->count() > 0;
    }
    public function is_praised_by_user($userId){
        static $caches = [];
        if(!array_key_exists($userId, $caches)){
            $caches[$userId] = \App\ArticlePraise::where('user_id', $userId)->where('article_id', $this->id)->count() > 0;
        }
        return $caches[$userId];
    }
    public function is_collected_by_user($userId){
        static $caches = [];
        if(!array_key_exists($userId, $caches)){
            $caches[$userId] = \App\ArticleCollection::where('user_id', $userId)->where('article_id', $this->id)->count() > 0;
        }
        return $caches[$userId];
    }
}
