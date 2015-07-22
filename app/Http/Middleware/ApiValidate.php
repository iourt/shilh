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
            return response()->json(['err' => 'xxss validate'], 500);
        }
        $response = $next($request)->header('Content-Type', "application/json");
        return $response;
	}

}
