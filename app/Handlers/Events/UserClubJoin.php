<?php namespace App\Handlers\Events;


use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserClubJoin {

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
	 * @param  UserClubJoin  $event
	 * @return void
	 */
	public function handle(\App\Events\UserClubJoin $event)
	{
        if(!$event->userId || !$event->clubId) return true;
        \App\Club::find($event->clubId)->increment('user_num');
	}

}
