<?php namespace App\Handlers\Events;

//use App\Events\UserArticlePraiseRemove;

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
	public function handle(\App\Events\UserArticlePraiseRemove $event)
	{
        if(!$event->userId || !$event->articleId) return true;

        \App\Article::where('id', $event->articleId)->update([
            'praise_num' =>  \App\ArticlePraise::where('article_id', $event->articleId)->count(),
        ]);
	}

}
