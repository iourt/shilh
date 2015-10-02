<?php namespace App\Http\Controllers;

//use App\Http\Requests;
//use Illuminate\Http\Response;
//use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use App\Http\Requests\CRequest AS Request; 
use App\Http\Controllers\ApiController;

class AdminController extends ApiController {

    public function getArticleList(Request $request){
        return $this->_render($request);
    }


};
