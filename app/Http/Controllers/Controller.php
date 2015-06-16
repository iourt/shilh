<?php namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;
    protected $auth;
    function __construct(){
        parent::__construct();
        $this->auth = array_merge(['id' => 0, 'role' => config('shilehui.role.guest'), 'auth' => '' ], session('user'));
    }
    public function isRoleOf($roleId){
        return $this->auth['role'] == $roleId;
    }

}
