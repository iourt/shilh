<?php
namespace App\Lib;
class Image {
    public static function getPathOfName($name){
//        substr($name,0,1)."/".substr($name,1,2)."/".$name;
        return substr($name,0,6);
    }
    public static function getTmpPathOfName($name){
        return "__temp";
    }
    public static function decodeAndSaveAsTmp($base64string, $userId){
        $res = array('size' => 0, 'ext' => '', 'name' => '',  'width' => 0, 'height' => 0, 'tempfile' => '', 'file'=>'');
        $name  = date("YmdHis")."_".$userId."_".md5($base64string);
        $imgBinStr = base64_decode($base64string); 
        if(!$imgBinStr) return $res;
        $imgData = getimagesizefromstring($imgBinStr);
        if(!$imgData) return $res;
        $ext = "";
        switch ($imgData[2]) {
            case IMAGETYPE_GIF: $ext = "gif";break;
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000: $ext = "jpg";break;
            case IMAGETYPE_PNG: $ext = "png";break;
        }

        if(!$ext) return $res;
        list($res['width'], $res['height']) = $imgData;
        $res['name'] = $name;
        $res['ext']  = $ext;
        $res['size'] = strlen($imgBinStr);

        $path = self::getTmpPathOfName($name);
        $file = sprintf("%s/%s.%s", $path, $name, $res['ext']);
        if(!\Storage::exists($path) ){
            \Storage::makeDirectory($path);
        }
        \Storage::put($file, $imgBinStr);
        return $res;

    }
    public static function moveToDestination($name, $ext){
        $tmpFile   = sprintf("%s/%s.%s", self::getTmpPathOfName($name), $name, $ext);
        $desPath   = self::getPathOfName($name);
        $desFile   = sprintf("%s/%s.%s", $desPath,  $name, $ext);
        /*
        if(!\Storage::exists($desPath)){
            \Storage::makeDirectory($desPath);
        }
         */
        if(\Storage::exists($tmpFile) ){
            \Storage::move($tmpFile, $desFile);
            return true;
        } else {
            return false;
        }
    }
}
