<?php namespace App\Http\Requests;
use Illuminate\Http\Request;
//http://stackoverflow.com/questions/30155500/extend-request-class-in-laravel-5
class CRequest extends Request {
    private function crAuth(){
        $auth  = new \App\Lib\Auth($this->crIsFromAPI() ? 'API' : 'PC', $this->crUserId() );
        return $auth;
    }
    public function crIsFromAPI() {
            return $this->is('api/*') || $this->is('mgapi/*');
    }
    public function crIsUserLogin() {
        if(env('APP_FAKEAUTH')){
            return true;
        }
        if($this->crIsFromAPI()) {
            $ok = $this->crAuth()->isLogin();
            $ok = $ok && $this->crAuth()->getAuthString() == $this->input('Header.Auth');
            return $ok;
        } else {
            return $this->crAuth()->isLogin();
        }
    }
    public function crIsUserRole($roleId) {
        return $this->crAuth()->isRoleOf($roleId);
    }
    public function crUserId() {
        if($this->crIsFromAPI()) {
            $h = array_merge(['UserId' => 0], $this->input('Header', []));
            $userId = $h['UserId'];
        } else {
            $userId = session('user.id');
        }
        return $userId;
    }
}
