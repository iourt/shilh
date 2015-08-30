<?php namespace App\Lib;

class Club {
    public static function render($club){
        if(empty($club)) return null;
        $arr = [
            'ClubId'   => $club->id,
            'ClubName' => $club->name,
            'ImageUrl' => empty($club->cover_image) ? '' : url($club->cover_image->url),
        ];
        return $arr;
    }
}


