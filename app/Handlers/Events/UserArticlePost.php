<?php namespace App\Handlers\Events;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserArticlePost {

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
	 * @param  UserArticlePost  $event
	 * @return void
	 */
	public function handle(\App\Events\UserArticlePost $event)
	{
        info(" - - Event Handle -- article id = ".$event->articleId." , type = ".$event->articleType);
        // create thumbnail for article images
        // notify 
	}

}
