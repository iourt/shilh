<?php namespace App\Http;
use Illuminate\Http\Request;
//http://stackoverflow.com/questions/30155500/extend-request-class-in-laravel-5
class CRequest extends Request{
    public $_AuthUser;
    public function user() {
        
    }
    public function userIsRole() {

    }
}
