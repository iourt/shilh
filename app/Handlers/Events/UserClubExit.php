<?php namespace App\Handlers\Events;

use App\Events\UserClubExit;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserClubExit {

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
	 * @param  UserClubExit  $event
	 * @return void
	 */
	public function handle(UserClubExit $event)
	{
		//
	}

}
