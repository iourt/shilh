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
        info("In Middleware: ".$request->url());
        if(!$request->isJson()){//!$request->ajax() || 
            return abort(404);
        }
        //if($request->is('^index") || $ 
		return $next($request);
	}

}
