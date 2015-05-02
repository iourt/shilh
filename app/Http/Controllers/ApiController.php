<?php namespace App\Http\Controllers;

use App\Http\Requests;
//use Illuminate\Http\Response;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ApiController extends Controller {
	public function __construct()
	{
	//	$this->middleware('api.auth');
	//	$this->middleware('api.parse');
	}
    public function getUserInfo(Request $request){
        return "RESPONSE:getUserInfo";
    }
    public function index(Request $request){
        return $this->_render([21,52,33,84, '中华人民共和国']);
    }
    private function _render($data, $type=null){
        return \Response::json($data);
    } 
}
