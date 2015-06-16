<?php namespace App\Http\Requests;
use Illuminate\Http\Request;
//http://stackoverflow.com/questions/30155500/extend-request-class-in-laravel-5
class CRequest extends Request {
    private function crAuth(){
        $auth  = new \App\Lib\Auth($this->crIsFromAPI() ? 'API' : 'PC', $this->crUserId() );
        return $auth;
    }
    public function crIsFromAPI() {
            return $this->is('api/*');
    }
    public function crIsUserLogin() {
        if($this->crIsFromAPI()) {
            $ok = $this->crAuth()->isLogin();
            \Log::info("login is ".$ok);
            $ok = $ok && $this->crAuth()->getAuthString() == $this->input('Header.Auth');
            \Log::info("login auth is ".$ok);
            return $ok;
        } else {
            return $this->crAuth()->isLogin();
        }
    }
    public function crIsUserRole($roleId) {
        return $this->crAuth()->isRoleOf($roleId);
    }
    public function crUserId() {
        var_dump($this->all());
        if($this->crIsFromAPI()) {
            $h=$this->input('Header', ['UserId'=>0]);
            $userId = $h['UserId'];
        } else {
            $userId = session('user.id');
        }
        \Log::info("user id is ".$userId);
        return $userId;
    }
}
