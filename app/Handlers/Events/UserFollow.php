<?php namespace App\Handlers\Events;

use App\Events\UserFollow;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserFollow {

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
	 * @param  UserFollow  $event
	 * @return void
	 */
	public function handle(UserFollow $event)
	{
		//
	}

}
