<?php
namespace App\Lib;
class Auth {
    public $userId;
    public $data;
    public $type;
    function __construct($type, $userId){
        $this->type   = $type;//PC/API
        if($this->isForPC()){
            $userId = session('user.id');
        }
        $this->userId = $userId;
    }
    public function isForAPI(){
        return $this->type == "API";
    }
    public function isForPC(){
        return $this->type == "PC";
    }
    public function getAuthString(){
        return md5($userId."\t".$lastLogin);
    }
    public function encryptPassword($password, $salt){
        return md5($salt."\t".$password);
    }
    public function setUserAuth(){
        $sessUser = ['id' => 0, 'role' => config('shilehui.role.guest'), 'auth' => ''];
        $user = \App\User::find($user_id);
        if(empty($user)){
            return $sessUser;
        }
        $authString = $this->makeAuthString($user->id, $user->challenge_id);
        $sessUser['id']   = $user->id;
        $sessUser['role'] = config('shilehui.role.user');
        $sessUser['auth'] = $authString;
        if($this->isForAPI()){
            session('user', $sessUser);
        } else  if ($this->isForPC()){
            $minutes = 60*24*30;
            \Cache::put('auth:'.$sessUser['id'], $sessUser, $minutes );
        }
        return $sessUser;
    }
    public function getUserAuth(){
        if($this->isForPc()){
            $auth = session('user', ['id' =>0, 'role' => config('shilehui.role.guest'), 'auth' => '']);
            $this->userId = $auth['id'];
            return $auth;
        } else if($this->isForAPI()) {
            return \Caches::get('auth:'.$userId,['id' =>0, 'role' => config('shilehui.role.guest'), 'auth' => '']);
        }
    }
    public function isRoleOf($roleId){
        $auth = $this->getUserAuth();
        return $auth['role'] == $roleId;
    }

    # validate user session
    # to delete them method
    public function validateUserAuth(){
        $auth = $this->getUserAuth();
        if(!$auth ||!array_key_exists('id', $auth) || !array_key_exists('role', $auth) || !array_key_exists('auth', $auth) ) {
            return false;
        }
        return true;
    }

    # verify if user is logon
    public function verifyUserAuth($request){
        $info = ['code' => 0, 'message' => '' ];
        if(!$this->validateUserAuth()){
            $info['code'] = 501;
            $info['message'] = 'need login again';
            return $info;
        }
        $auth = $this->getUserAuth();
        if(!$auth['id'] || !$auth['role'] || !$auth['auth']){
            $info['code'] = 502;
            $info['message'] = 'not login';
            return $info;
        }
        $header = $request->input('Header');
        if(!$header['UserId'] || $auth['user']['id'] != $header['UserId']){
            $info['code'] = 503;
            $info['message'] = 'wrong user id';
            return $info;
        }
        if(!$header['Auth'] || $auth['user']['auth'] != $header['Auth']){
            $info['code'] = 504;
            $info['message'] = 'wrong user auth';
            return $info;
        }
        # calc the auth string according to db data, and then check if sesseion[user][auth] is right
        # if(env('APP_FAKEAUTH'))
        
        return $info;
        
    }
}
