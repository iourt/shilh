<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ImageController extends Controller {
    protected $defaultImageConfig;
    protected $defaultImage;
    public function __construct(){
        parent::__construct();
        $this->defaultImageConfig = config('shilehui.default_image');
    }
    public function _render($storageFile){
        //info('render '.$storageFile);
        if(!\Storage::exists($storageFile)){
            $content = file_get_contents($this->defaultImage);
            $mime    = 'image/jpeg';
            //return response()->download($this->defaultImage);
        } else {
            $content = \Storage::get($storageFile);
            $mime    = \Storage::mimeType($storageFile);
        }
        return response($content)->header("Content-Type", $mime);
    }

    public function article($articleId, $imageId, $imageExt){
        $this->defaultImage = public_path()."/".$this->defaultImageConfig['article'];
        $image = \App\ArticleImage::where('article_id', $articleId)->where('id', $imageId)->first();
        if(empty($image)){
            $file = $this->defaultImage;
        }else{
            $file = $image->storage_file;
        }
        return $this->_render($file);
    }
    public function user($userId, $imageId, $imageExt){
        $this->defaultImage = public_path()."/".$this->defaultImageConfig['user'];
        $image = \App\UserImage::where('user_id', $userId)->where('id', $imageId)->first();
        if(empty($image)){
            $file = $this->defaultImage;
        }else{
            $file = $image->storage_file;
        }
        return $this->_render($file);
    }
    public function cover($imageId, $imageExt){
        $this->defaultImage = public_path()."/".$this->defaultImageConfig['cover'];
        $image = \App\CoverImage::where('id', $imageId)->first();
        if(empty($image)){
            $file = $this->defaultImage;
        }else{
            $file = $image->storage_file;
        }
        return $this->_render($file);
    }

}
