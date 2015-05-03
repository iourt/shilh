<?php
namesapce App\Lib;
class Auth {
    public static function makeAuthString($user_id, $last_login){
        return md5($user_id."\t".$last_login);
    }
    public static function encryptPassword($password, $salt){
        return md5($salt."\t".$password);
    }
}
