<?php namespace App\Handlers\Events;

use App\Events\UserClubJoin;

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
	public function handle(UserClubJoin $event)
	{
		//
	}

}
