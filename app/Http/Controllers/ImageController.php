<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ImageController extends Controller {
    public function _render($type, $storageFile, $appendText=""){
        if(!$storageFile || !\Storage::exists($storageFile)){
            $mime = 'image/png';
            $content = file_get_contents(public_path().'/'.config('shilehui.default_image.'.$type));
            if($appendText){
                $im = imagecreatefromstring($content);
                imagestring($im, 3, 5, 5, $appendText, imagecolorallocate($im, 233,14,91));
                ob_start();
                imagepng($im);
                $content = ob_get_clean();
                imagedestroy($im);
            } 
        } else {
            $content = \Storage::get($storageFile);
        //    $mime    = \Storage::mimeType($storageFile);
            $mime = $this->_getMimeFromFilename($storageFile);
        }
        return response($content)->header("Content-Type", $mime);
    }

    private function _getMimeFromFilename($file){
        $mime_types = [
            "pdf"=>"application/pdf"
            ,"exe"=>"application/octet-stream"
            ,"zip"=>"application/zip"
            ,"docx"=>"application/msword"
            ,"doc"=>"application/msword"
            ,"xls"=>"application/vnd.ms-excel"
            ,"ppt"=>"application/vnd.ms-powerpoint"
            ,"gif"=>"image/gif"
            ,"png"=>"image/png"
            ,"jpeg"=>"image/jpg"
            ,"jpg"=>"image/jpg"
            ,"mp3"=>"audio/mpeg"
            ,"wav"=>"audio/x-wav"
            ,"mpeg"=>"video/mpeg"
            ,"mpg"=>"video/mpeg"
            ,"mpe"=>"video/mpeg"
            ,"mov"=>"video/quicktime"
            ,"avi"=>"video/x-msvideo"
            ,"3gp"=>"video/3gpp"
            ,"css"=>"text/css"
            ,"jsc"=>"application/javascript"
            ,"js"=>"application/javascript"
            ,"php"=>"text/html"
            ,"htm"=>"text/html"
            ,"html"=>"text/html"
        ];
        $extension = strtolower(preg_replace('/^.*\./','', $file));
        return $mime_types[$extension]; 
    }

    public function article($articleId, $imageId, $imageExt){
        $image = \App\ArticleImage::where('article_id', $articleId)->where('id', $imageId)->first();
        $file = empty($image) ? "" : $image->storage_file;
        return $this->_render('article', $file, $appendText = date("i:s ").$articleId.'/'.$imageId);
    }
    public function article_thumb($articleId, $imageId, $imageExt){
        $image = \App\ArticleImage::where('article_id', $articleId)->where('id', $imageId)->first();
        $thumb_file = empty($image) ? "" : $image->storage_thumb_file;
        $file       = empty($image) ? "" : $image->storage_file;
        \Log::info("begin thumb");
        if($file && \Storage::exists($file)){// && !\Storage::exists($thumb_file)){
            \Log::info("need create thumb");
            $this->makeThumb($file, $thumb_file, $image->thumb_width, $image->thumb_height);
        }
        $file = $thumb_file;
        return $this->_render('article_thumb', $file, $appendText = $file."\n".date("i:s ").$articleId.'/'.$imageId);
    }
    public function user($userId, $imageId, $imageExt){
        $image = \App\UserAvatar::where('user_id', $userId)->where('id', $imageId)->first();
        $file = empty($image) ? "" : $image->storage_file;
        return $this->_render('user', $file, "U");
    }
    public function cover($imageId, $imageExt){
        $image = \App\CoverImage::where('id', $imageId)->first();
        $file = empty($image) ? "" : $image->storage_file;
        return $this->_render('cover', $file, "C");
    }
    public function banner($imageId, $imageExt){
        $image = \App\Banner::where('id', $imageId)->first();
        $file = empty($image) ? "" : $image->storage_file;
        return $this->_render('banner', $file, "B/".$imageId);
    }
    public function makeThumb($origFile, $thumbFile, $width, $height){
        $storageRoot = storage_path()."/app";
        $arr_image_details = getimagesize($storageRoot."/".$origFile);
        $original_width    = $arr_image_details[0];
        $original_height   = $arr_image_details[1];
        if($original_width <= $width){
            \Storage::copy($origFile, $thumbFile);
            return;
        } 
        $new_width = $width;
        $new_height = $height;
        $dest_x = 0;
        $dest_y = 0;
        if ($arr_image_details[2] == 1) {
            $imgt = "ImageGIF";
            $imgcreatefrom = "ImageCreateFromGIF";
        }
        if ($arr_image_details[2] == 2) {
            $imgt = "ImageJPEG";
            $imgcreatefrom = "ImageCreateFromJPEG";
        }
        if ($arr_image_details[2] == 3) {
            $imgt = "ImagePNG";
            $imgcreatefrom = "ImageCreateFromPNG";
        }
        if ($imgt) {
            $old_image = $imgcreatefrom($storageRoot."/".$origFile);
            $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
            imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
            //$imgt($new_image, $storageRoot."/".$thumbFile);
            imagepng($new_image, $storageRoot."/".$thumbFile, 0);
        }

    }

}
