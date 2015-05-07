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
    private function _render($data, $responseData=[], $type=null){
        $this->data['Response'] = array_merge($this->data['Response'], $responseData);
        $output = array_merge($this->data, $data);
        return response()->json($output);
    } 
    private function _validate($request, $rules, $resData){
        $v = \Validator::make($request->all(), $rules);
        if($v->fails()){
            throw new \App\Exceptions\ApiValidateException(response()->json([
                'time'=>time(), 'state' => $resData['State'], 'Ack'=>'Success', 'ErrMsg'=>$v->messages()], 500));
        }
    }
    public function getUserInfo(Request $request){
        $this->_validate($request, [
            'UserId' => 'requried|exists:users,id',
            ],['State' => 201]);
        $user = \App\User::find($request->input('UserId'));
        if(empty($user)){
            return $this->_render([], ['State' => 201]);
        }
        return $this->_render([

            ]);
    }
    public function index(Request $request){
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
        //if($user->encrypt_password != \App\Lib\Auth::encryptPassword($password, $user->salt)){
        if($user->encrypt_password != $password){
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
            'UserName'    => 'required|string|min:2,max:32',
            'Sex'         => 'required|in:'.implode(",", config('shilehui.sex')),
            'Area'        => 'required|exists:areas,id',
            'Job'         => 'required|exists:jobs,id',
            'Phone'       => 'required|',
            'Password'    => 'required',
            ], ['State'=>201]);
        $user = \App\User::where('phone', $request->input('Phone'));
        if($user) {
            return $this->_render([], ['State' => 202]);
        }
        //$user = \App\User::firstOrNew(['phone', $request->input('Phone')]);
        $user = new \App\User;
        $user->sex = $request->input('Sex');
        $user->area_id = $request->input('Sex');
        $user->job_id = $request->input('Sex');
        $user->name = $request->input('Sex');
        $user->password = $request->input('Sex');
        $res = $user->save();
        return $this->_render(['UserId'=>$user->id ]);
    }
    public function getCityList(Request $request) {
        $list = \App\Lib\Area::all();
        return $this->_render($list);
    }
}
