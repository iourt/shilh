<?php
namespace App\Lib;
class Image {
    public static function getFileByName($name){
//        substr($name,0,1)."/".substr($name,1,2)."/".$name;
        substr($name,0,6)."/".$name;
    }
}
