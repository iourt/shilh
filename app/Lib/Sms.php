<?php
namespace App\Lib;

class Sms {
    public static function sendVerifyCode($type, $phone, $code){
        $gw = config('shilehui.sms_api.gateway');
        $text="";
        if( $type == config('shilehui.verify_code.fetch_password.id') ){
            $text = '取回密码的验证码是'.$code.',有效期'.(config('shilehui.verify_code.fetch_password.seconds')/60).'分钟';
        }
        $gw = str_replace( ["{mobile}", "{content}", "{sendtime}"], [$phone, $text, ""], $gw);
        $xml = @simplexml_load_string( file_get_contents($gw) );
        if($xml && 'Success' == $xml->returnstatus){
            \Log::info("[SMS.FETCHPASSWORD]{mobile:$phone, code:$code, success:true}");
        } else {
            \Log::warning("[SMS.FETCHPASSWORD]{mobile:$phone, code:$code, success:false}");
            \Log::warning($gw);
        }
    }
}

