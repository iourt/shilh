<?php
namespace App\Lib;
class Auth {
    public static function makeAuthString($userId, $lastLogin){
        return md5($userId."\t".$lastLogin);
    }
    public static function encryptPassword($password, $salt){
        return md5($salt."\t".$password);
    }
    public static function setUserAuth($user_id){
        $sessUser = ['id' => 0, 'role' => config('shilehui.role.guest'), 'auth' => ''];
        $user = \App\User::find($user_id);
        if(empty($user)){
            throw new Exception();
        }
        $authString = Auth::makeAuthString($user->id, $user->challenge_id);
        $sessUser['id']   = $user->id;
        $sessUser['role'] = config('shilehui.role.user');
        $sessUser['auth'] = $authString;
        Session::put('user', $sessUser);
        return $sessUser;
    }
    public static function getUserAuth(){
        return session('user');
    }

    # validate user session
    public static function validateUserAuth(){
        $auth = Auth::getUserAuth();
        if(!$auth ||!array_key_exists('id', $auth) || !array_key_exists('role', $auth) || !array_key_exists('auth', $auth) ) {
            return false;
        }
        return true;
    }

    # verify if user is logon
    public static function verifyUserAuth($request){
        $info = ['code' => 0, 'message' => '' ];
        if(!Auth::validateUserAuth()){
            $info['code'] = 501;
            $info['message'] = 'need login again';
            return $info;
        }
        $auth = Auth::getUserAuth();
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
