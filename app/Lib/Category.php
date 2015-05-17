<?php namespace App\Lib;

class Category {
    public static function getBreadcrumb($id){
        $arr = \Cache::rememberForever('CATEGORY_BREADCRUMB', function(){
            return \App\Lib\Category::makeBreadcrumbs();
        });
        if($arr && array_key_exists($id, $arr)){
            return $arr[$id];
        } else {
            return [];
        }
    }
    public static function makeBreadcrumbs(){
        $arr = [];
        $colls = \App\Category::orderBy('level', 'asc')->get();
        foreach($colls as $c){
            $v = ['id'=>$c->id, 'name'=>$c->name];
            if($c->parent_id!=0){
                $arr[$c->id] = array_merge($arr[$c->parent_id], [$v]);
            } else {
                $arr[$c->id] = [$v];
            }
        }
        return $arr;
    
    }
    public static function renderBreadcrumb($id) {
        $cates = \App\Lib\Category::getBreadcrumb($id);
        $cateList = [];
        foreach($cates as $ct){
            $cateList[] = ['CateId'=>$ct['id'], 'CateName'=>$ct['name']];
        }
        return $cateList;
    }
}


