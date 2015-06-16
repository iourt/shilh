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
        $info = \App\Lib\Auth::verifyUserAuth($request);
        $output = ['Response' => ['Time'=>time(), 'State' => false, 'Ack' => 'failure']];
        if($info['code'] !=0 ) {
            $output['Response']['Message'] = $info['message'];
            return response()->json($output, $info['code']);
        }
        return $next($request);
	}
}
