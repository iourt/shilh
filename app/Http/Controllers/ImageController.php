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
            $mime    = \Storage::mimeType($storageFile);
        }
        return response($content)->header("Content-Type", $mime);
    }

    public function article($articleId, $imageId, $imageExt){
        $image = \App\ArticleImage::where('article_id', $articleId)->where('id', $imageId)->first();
        $file = empty($image) ? "" : $image->storage_file;
        return $this->_render('article', $file, $appendText = date("i:s ").$articleId.'/'.$imageId);
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

}
