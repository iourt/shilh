<?php namespace App\Handlers\Events;

use App\Events\UserChat;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserChat {

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
	 * @param  UserChat  $event
	 * @return void
	 */
	public function handle(UserChat $event)
	{
		//
	}

}
