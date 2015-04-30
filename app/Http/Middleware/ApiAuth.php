<?php namespace App\Http\Middleware;

use Closure;

class AuthUser {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        $route = $request->route();
        info("route is $route");
        if(!$request->isJson()){//!$request->ajax() || 
            return abort(404);
        }
        //if($request->is('^index") || $ 
		return $next($request);
	}

}
