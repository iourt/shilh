<?php namespace App\Http\Controllers;

use App\Http\Requests;
//use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller {
    protected $data;
	public function __construct() {
        parent::__construct();
        $this->data = [ 'Response' => [
            'Time'  => time(),
            'State' => 200,
            'Ack'   => 'Success',
            ]];
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
        var_dump($this);
        return $this->_render([]);
    }
    public function getLogin(Request $request){
        $this->_validate($request, [
            'Phone'    => 'required|numeric',
            'Password' => 'required',
            ], ['State'=>201]);

        $user = \App\User::where('phone', $request->get('Phone'));
        if(empty($user)){
            return $this->_render(['State' => 202]);
        }
        $password = $request->get('Password');
        //$password = \App\Lib\Auth::descrpt_password($request->get('Password'));
        if($user->encrypt_password != \App\Lib\Auth::encryptPassword($password, $user->salt)){
            return $this->_render(['State' => 202]);
        }
        $user->challenge_id = time();
        $user->save();
        $authString = \App\Lib\Auth::makeAUthString($user->id, $user->challenge_id);
        $sessUser = ['id' => $user->id, 'role' => 0, 'auth' => $authString];
        Session::put('user', $sessUser);
        return $this->_render(['UserId' => $user->id, 'Auth' => $authString]);
    }
    public function getLogout(Request $request) {
    }
    public function setRegInfo(Request $request) {
        $this->_validate($request, [
            'Phone'    => 'required|numeric',
            'Password' => 'required',
            ], ['State'=>201]);
    }
    public function getCityList(Request $request) {
        $list = \App\Lib\Area::all();
        return $this->_render($list);
    }
}
