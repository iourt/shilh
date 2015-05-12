<?php namespace App\Handlers\Events;

use App\Events\UserArticlePraiseAdd;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserArticlePraiseAdd {

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
	 * @param  UserArticlePraiseAdd  $event
	 * @return void
	 */
	public function handle(UserArticlePraiseAdd $event)
	{
		//
	}

}
