<?php namespace App\Handlers\Events;

use App\Events\UserArticleCollectionAdd;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserArticleCollectionAdd {

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
	 * @param  UserArticleCollectionAdd  $event
	 * @return void
	 */
	public function handle(UserArticleCollectionAdd $event)
	{
		//
	}

}
