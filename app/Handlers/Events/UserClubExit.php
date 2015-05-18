<?php namespace App\Handlers\Events;

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
	public function handle(\App\Events\UserClubExit $event)
	{
        if(!$event->userId || !$event->clubId) return true;
        \App\Club::find($event->clubId)->decrement('user_num');
	}

}
