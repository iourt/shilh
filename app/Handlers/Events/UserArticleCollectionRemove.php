<?php namespace App\Handlers\Events;

use App\Events\UserArticleCollectionRemove;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserArticleCollectionRemove {

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
	 * @param  UserArticleCollectionRemove  $event
	 * @return void
	 */
	public function handle(UserArticleCollectionRemove $event)
	{
		//
	}

}
