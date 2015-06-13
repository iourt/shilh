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
        $auth = config('_auth');
        if(env('APP_FAKEAUTH')){
            $authStr = $auth['header']['Auth'];
        } else {
            $authStr = \App\Lib\Auth::makeAuthString(200, '204-15-20 00:00:03');
        }
        if(!$auth['user']['id'] || $auth['user']['id'] != $auth['header']['UserId']){
            throw new \App\Exceptions\ApiException(['errorMessage'=>'wrong user info '], 501);
        }
        if($authStr != $auth['header']['Auth']){
            throw new \App\Exceptions\ApiException(['errorMessage'=>'wrong auth info  '], 503);
        }
        return $next($request);
	}
}
