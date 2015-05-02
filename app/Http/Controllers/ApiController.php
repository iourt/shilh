<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ApiController extends Controller {
	public function __construct()
	{
		$this->middleware('api.auth');
	//	$this->middleware('api.parse');
	}
    public function getUserInfo(){
        return "RESPONSE:getUserInfo";
    }
    public function index(){
        $array = Request::input();
        var_dump($array);
        return "RESPONSE:index";
    }
}
