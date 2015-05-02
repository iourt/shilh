<?php
namesapce App\Lib;
class Auth {
    public static function makeAuthString($user_id, $last_login){
        return md5($user_id."x".$last_login);
    }
}
