<?php namespace App\Http\Middleware;

use Closure;

class ApiAuth {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        return $next($request);
        $auth = config('_auth');
        if(!$auth['user']['id'] || $auth['user']['id'] != $auth['header']['userId']){
            return abort(501);
        }
        $authStr = \App\Lib\Auth::makeAuthString(200, '204-15-20 00:00:03');
        if($authStr != $auth['header']['auth']){
            return abort(503);
        }
        return $next($request);
	}

}
