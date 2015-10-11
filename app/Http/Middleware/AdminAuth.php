<?php namespace App\Http\Middleware;

use Closure;

class AdminAuth {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
        if( !$request->crIsUserLogin() 
            || $request->crIsUserRole(config('shilehui.role.ban'))  
            || !$request->crIsUserRole(config('shilehui.role.admin'))
        ) {
            return response()->json(['Response' => ['Time'=>time(), 'State' => false, 'Ack' => 'failure', "Err"=>'auth fail']], 200);
        }
        return $next($request);
	}
}
