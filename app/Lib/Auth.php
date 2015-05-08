<?php
namesapce App\Lib;
class Auth {
    public static function makeAuthString($userId, $lastLogin){
        return md5($userId."\t".$lastLogin);
    }
    public static function encryptPassword($password, $salt){
        return md5($salt."\t".$password);
    }
    public static function setUserAuth($user_id){
        $user = \App\User::find($user_id);
        if(empty($user)){
            throw new Exception();
        }
        $authString = Auth::makeAUthString($user->id, $user->challenge_id);
        $sessUser = ['id' => $user->id, 'role' => 0, 'auth' => $authString];
        Session::put('user', $sessUser);
    }
}
