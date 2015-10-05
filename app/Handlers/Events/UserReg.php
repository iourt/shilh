<?php namespace App\Handlers\Events;

use App\Events\UserReg;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserReg {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  UserReg  $event
	 * @return void
	 */
	public function handle(UserReg $event)
	{
		//
	}

}
