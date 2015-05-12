<?php namespace App\Handlers\Events;

use App\Events\UserArticlePraiseRemove;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserArticlePraiseRemove {

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
	 * @param  UserArticlePraiseRemove  $event
	 * @return void
	 */
	public function handle(UserArticlePraiseRemove $event)
	{
		//
	}

}
