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
        info("IN.Api.Validate ".$request->path());
        if(!$request->isJson()){//!$request->ajax() || 
            return abort(500);
        }

        $user   = array_merge(['id'=>0], session('user', []));
        $header = array_merge(['userId' => 0, 'auth' => ''], $request->get('Header', []));

        \Config::set('_auth', ['user' => $user, 'header' => $header]);
        $response = $next($request);
        //$response->header("Login-User-Id", 4567);
        return $response;
        //$next($request)->header("content-type", "application/json;charset=utf8")
        /*
        $next($request)
            ->header("Access-Control-Allow-Origin", "http://www.d.com")
            ->header("Login-User-Id", 4567);
         */
	}

}
