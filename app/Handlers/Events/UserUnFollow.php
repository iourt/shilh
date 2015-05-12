<?php namespace App\Handlers\Events;

use App\Events\UserUnFollow;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserUnFollow {

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
	 * @param  UserUnFollow  $event
	 * @return void
	 */
	public function handle(UserUnFollow $event)
	{
		//
	}

}
