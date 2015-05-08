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
            'UserId' => 'required|exists:users,id',
            ],['State' => 201]);
        $isViewMine = $request->input('UserId') == $this->auth['user']['id'];
        $user = \App\User::find($request->input('UserId'));
        if(empty($user)){
            return $this->_render([], ['State' => 201]);
        }
        return $this->_render([
            'UserImage' => $user->user_image_file,
            'Username'  => $user->name,
            'Exper'     => $user->exp_num,
            'RankName'  => '',
            'TotalFollow' => $user->follow_num,
            'TotalFans'   => $user->fans_num,
            'ArticleList' => [],
            'CollectList' => [],
            'ClubList'    => [],
            'TotalCollect' => $user->collect_num,
            'TotalArticle' => $user->article_num,
            'TotalClub'    => $user->club_num,
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

        $user = \App\User::where('mobile', $request->get('Phone'))->first();
        if(empty($user)){
            return $this->_render([], ['State' => 202]);
        }
        $password = $request->get('Password');
        //$password = \App\Lib\Auth::descrpt_password($request->get('Password'));
        //if($user->encrypt_password != \App\Lib\Auth::encryptPassword($password, $user->salt)){
        if($user->encrypt_pass != $password){
            return $this->_render([], ['State' => 203]);
        }
        $user->challenge_id = time();
        $user->save();
        \App\Lib\Auth::setUserAuth($user->id);
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
        $user = \App\User::where('mobile', $request->input('Phone'))->first();
        if($user) {
            return $this->_render([], ['State' => 202]);
        }
        //$user = \App\User::firstOrNew(['mobile', $request->input('Phone')]);
        $user = new \App\User;
        $user->mobile = $request->input('Phone');
        $user->sex = $request->input('Sex');
        $user->area_id = $request->input('Area');
        $user->job_id = $request->input('Job');
        $user->name = $request->input('UserName');
        $user->encrypt_pass = $request->input('Password');
        $res = $user->save();
        return $this->_render(['UserId'=>$user->id ]);
    }
    public function getCityList(Request $request) {
        $list = \App\Lib\Area::all();
        return $this->_render($list);
    }
}
