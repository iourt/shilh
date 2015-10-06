<?php namespace App\Handlers\Events;

//use App\Events\UserClubAttend;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserClubAttend {

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
	 * @param  UserClubAttend  $event
	 * @return void
	 */
	public function handle(\App\Events\UserClubAttend $event)
	{
        //TODO 积分？
	}

}
