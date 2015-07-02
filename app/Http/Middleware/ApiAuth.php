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
        if(!$request->crIsUserLogin()) {
            return response()->json(['Response' => ['Time'=>time(), 'State' => false, 'Ack' => 'failure', "Err"=>'auth fail']], 500);
        }
        return $next($request);
	}
}
