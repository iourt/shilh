<?php namespace App\Http\Requests;
use Illuminate\Http\Request;
//http://stackoverflow.com/questions/30155500/extend-request-class-in-laravel-5
class CRequest extends Request {
    public function crIsUserLogin() {
        static $ok = null;
        if($ok === null ) {
            $ok = true;
        }
        return $ok;
    }
    public function crIsUserRole($roleId) {
        static $ok = null;
        if($ok === null) {
            $ok = true;
        }
        return $ok;
    }
    public function crUserId() {
        static $userId = null;
        if($userId === null) {
            if($this->is('api/*')) {
                $userId = $this->input('Header.UserId');
            } else {
                $userId = session('user.id');
            }
        }
        return $userId;
    }
}
