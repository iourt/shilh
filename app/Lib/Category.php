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
            $v = ['CateId'=>$c->id, 'CateName'=>$c->name];
            if($c->parent_id!=0){
                $arr[$c->id] = array_merge($arr[$c->parent_id], [$v]);
            } else {
                $arr[$c->id] = [$v];
            }
        }
        return $arr;

    }
    public static function renderBreadcrumb_old($id) {
        $cates = \App\Lib\Category::getBreadcrumb($id);
        $cateList = [];
        foreach($cates as $ct){
            $cateList[] = ['CateId'=>$ct['CateId'], 'CateName'=>$ct['CateName']];
        }
        return $cateList;
    }
    public static function renderBreadcrumb($id){
        static $caches = [];
        if(!array_key_exists($id, $caches)){
            $cates = \App\Lib\Category::getAncestorsOf($id);
            $cateList = [];
            foreach($cates as $c){
                $cateList[] = [ 'CateId' => $c->id, 'CateName' => $c->name ];
            }
            $caches[$id]=$cateList;
        }
        return $caches[$id];
    }


    public static function getAncestorsOf($id){
        $sqls = ["select c1.id as c1_id"];
        for($i=2; $i <= config('shilehui.category_max_level'); $i++){
            $sqls[] = ", c${i}.id as c${i}_id";
        }
        $sqls[] = " from categories c1 ";
        for($i=2; $i <= config('shilehui.category_max_level'); $i++){
            $sqls[] = " left join categories c${i} on (c${i}.id = c".($i-1).".parent_id)";
        }
        $sqls[]="where c1.id=:id";
        $arr = \DB::select(\DB::raw(implode(" ", $sqls)), array('id' => $id) );
        $ids = [];
        foreach($arr as $obj){
            for($i=1; $i <= config('shilehui.category_max_level'); $i++){
                $t = "c${i}_id";
                $ids[] = $obj->$t;
            }
        }
        return \App\Category::whereIn('id',$ids)->orderBy('level', 'asc')->get();
    }

    public static function getChildrenOf($id){
        return \App\Category::where('parent_id', $id)->get();
    }

    public static function getDescendantsOf($id){
        $sqls = ["select c1.id as c1_id"];
        for($i=2; $i <= config('shilehui.category_max_level'); $i++){
            $sqls[] = ", c${i}.id as c${i}_id";
        }
        $sqls[] = " from categories c1 ";
        for($i=2; $i <= config('shilehui.category_max_level'); $i++){
            $sqls[] = " left join categories c${i} on (c${i}.parent_id = c".($i-1).".id)";
        }
        $sqls[]="where c1.id=:id";
        $arr = \DB::select(\DB::raw(implode(" ", $sqls)), array('id' => $id) );
        $ids = [];
        foreach($arr as $obj){
            for($i=1; $i <= config('shilehui.category_max_level'); $i++){
                $t = "c${i}_id";
                $ids[] = $obj->$t;
            }
        }
        return \App\Category::whereIn('id',$ids)->orderBy('level', 'asc')->get();
    }
    public static function renderDetail($cate){
        if(empty($cate)) return null;
        $arr = [
            'CateId'   => $cate->id,
            'CateName' => $cate->name,
            'ImageUrl' => empty($cate->cover_image) ? '' : url($cate->cover_image->url),
            'Description' => $cate->brief,
            'ClubList'    => [],
        ];
        foreach($cate->clubs()->get() as $club){
            $arr['ClubList'][] = \App\Lib\Club::render($club);
        }
        return $arr;
    }
    public static function render($cate){
        if(empty($cate)) return null;
        $arr = [
            'CateId'   => $cate->id,
            'CateName' => $cate->name,
            'ImageUrl' => empty($cate->cover_image) ? '' : url($cate->cover_image->url),
        ];
        return $arr;
    }
}


