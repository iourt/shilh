<?php namespace App\Lib;

class Area {
    public static function provinces(){
    }
    public static function cities($provinceId){
    }
    public static function counties($cityId){
    }
    public static function all(){
        $areas = \App\Area::orderBy('level', 'asc')->get();
        $list = ['province' => [], 'city' => [], 'county' => []];
        foreach($areas as $area){
            if($area->is_province)
                $list['province'][] = ['id' => $area->id, 'name' => $area->name];
            if($area->is_city)
                $list['city'][] = ['id' => $area->id, 'name'=> $area->name, 'province_id' => $area->parent_id];
            if($area->is_county)
                $list['county'][] = ['id' => $area->id, 'name'=> $area->name, 'city_id' => $area->parent_id];
        }
        return $list;
    }
}
