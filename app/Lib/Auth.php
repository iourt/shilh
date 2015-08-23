<?php
namespace App\Lib;
class Auth {
    public $user;
    public $data;
    public $type;
    function __construct($type, $userId, $needRefresh = false){
        static $u = null;
        $this->type   = $type;//PC/API
        if($this->accessFromPC()){
            $userId = session('user.id');
        }
        if($u === null || $needRefresh ){
            $u = \App\User::find($userId);
        }
        $this->user = $u;
    }
    public function accessFromAPI(){
        return $this->type == "API";
    }
    public function accessFromPC(){
        return $this->type == "PC";
    }
    public function getAuthString(){
        if(empty($this->user)) return "";
        return md5($this->user->id."\t".$this->user->challenge_id);
    }
    public static function encryptPassword($password, $salt){
        return md5($salt."\t".$password);
    }
    public function setUserAuth(){
        $sessUser = ['id' => 0, 'role' => config('shilehui.role.guest'), 'auth' => ''];
        if(empty($this->user)){
            return $sessUser;
        }
        $authString = $this->getAuthString();
        $sessUser['id']   = $this->user->id;
        $sessUser['role'] = config('shilehui.role.user');
        $sessUser['auth'] = $authString;
        if($this->accessFromPC()){
            session('user', $sessUser);
        } else if ($this->accessFromAPI()){
            $minutes = config('shilehui.auth.api_minutes');
            \Cache::put('auth:'.$sessUser['id'], $sessUser, $minutes );
        }
        return $sessUser;
    }
    public function removeUserAuth(){
        $sessUser = ['id' => 0, 'role' => config('shilehui.role.guest'), 'auth' => ''];
        if(empty($this->user)){
            return $sessUser;
        }
        $userId   = $this->user->id;
        if($this->accessFromPC()){
            session('user', $sessUser);
        } else if ($this->accessFromAPI()){
            $minutes = 60*24*30;
            \Cache::put('auth:'.$userId, $sessUser, $minutes );
        }
        return $sessUser;
    }
    //get auth info from session/cache, then compare it with db
    public function getUserAuth(){
        $auth_default  =  ['id' =>0, 'role' => config('shilehui.role.guest'), 'auth' => ''];
        if(empty($this->user)) return $auth_default;
        if($this->accessFromPc()){
            $auth = session('user', $auth_default);
        } else if($this->accessFromAPI()) {
            $userId = empty($this->user) ? 0 : $this->user->id;
            $auth = \Cache::get('auth:'.$userId, $auth_default);
        }
        if($auth['id'] != $this->user->id || $auth['auth'] != $this->getAuthString() ){
            $auth = $auth_default;
        }
        return $auth;
    }
    public function isRoleOf($roleId){
        $auth = $this->getUserAuth();
        return $auth['role'] == $roleId;
    }

    //check if auth data in cache/session is login-status
    public function isLogin(){
        $auth = $this->getUserAuth();
        return $auth['id'] != 0;
    }

}
