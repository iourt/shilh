<?php namespace App\Http\Middleware;

use Closure;
//use Illuminate\Http\Response;

class ApiValidate {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        if(!$request->isJson() ){//!$request->ajax() || 
            return abort(500);
        }

        $user   = array_merge(['id'=>0], session('user', []));
        $header = array_merge(['UserId' => 0, 'Auth' => ''], $request->get('Header', []));
        if(env('APP_FAKEAUTH')) {
            $user['id'] = $header['UserId'];
        }
        \Config::set('_auth', ['user' => $user, 'header' => $header]);
        $response = $next($request)->header('Content-Type', "application/json")->header('Access-Control-Allow-Origin', '*');
        return $response;
	}

}
