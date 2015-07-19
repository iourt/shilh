<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model {

    protected $guarded = [];
    function getIsProvinceAttribute($value){
        return $this->level == config('shilehui.area_level')['province'];
    }
    function getIsCityAttribute($value){
        return $this->level == config('shilehui.area_level')['city'];
    }
    function getIsCountyAttribute($value){
        return $this->level == config('shilehui.area_level')['county'];
    }

}
