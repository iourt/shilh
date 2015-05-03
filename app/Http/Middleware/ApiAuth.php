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
        info("IN.Api.Auth ".$request->path());
        if(!$request->has('head') || !$request->has('head.UserId') || !$request->has('head.Auth')){
            abort(500);
        }
        if(!Session::has('user') || !Session::get('user.id')){
            abort(500);
        }
        $auth = App\Lib\Auth::makeAuthString(200, '204-15-20 00:00:03');
        if($auth! = $request->get('head.Auth')){
            abort(500);
        }
        return $next($request);
	}

}
