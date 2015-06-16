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
            throw new \App\Exceptions\ApiException(['errorMessage'=>'not application/json header'], 500);
        }
        $ok = \App\Lib\Auth::validateUserAuth();;
        if(!$ok){
            throw new \App\Exceptions\ApiException(['errorMessage'=>'internal error '], 500);
        }
        $response = $next($request)->header('Content-Type', "application/json")->header('Access-Control-Allow-Origin', '*');
        return $response;
	}

}
