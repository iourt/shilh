<?php namespace App\Http\Controllers;

use App\Http\Requests;
//use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller {
    private $data;
	public function __construct()
    {
        $this->data = [ 'Response' => [
            'Time'  => time(),
            'State' => 200,
            'Ack'   => 'Success',
            ]];
	//	$this->middleware('api.auth');
	//	$this->middleware('api.parse');
    //
	}
    private function _render($data, $type=null){
        $output = array_merge($this->data, $data);
        return response()->json($output);
    } 
    private function _validate($request, $rules, $resData){
        $v = \Validator::make($request->all(), $rules);
        if($v->fails()){
            throw new \App\Exceptions\ApiValidateException(response()->json([
                'time'=>time(), 'state' => $resData['State'], 'Ack'=>'Success'], 500));
        }
    }
    public function getUserInfo(Request $request){
        return "RESPONSE:getUserInfo";
    }
    public function index(Request $request){
        return $this->_render([21,52,33,84, '中华人民共和国']);
    }
    public function getLogin(Request $request){
        $this->_validate($request, [
            'Phone'    => 'required|number',
            'Password' => 'required',
            ], ['State'=>201]);
        return $this->_render(['Test'=>222]);
    }
}
