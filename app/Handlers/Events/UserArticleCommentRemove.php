<?php namespace App\Handlers\Events;

//use App\Events\UserArticleCommentRemove;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserArticleCommentRemove {

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
	 * @param  UserArticleCommentRemove  $event
	 * @return void
	 */
	public function handle(\App\Events\UserArticleCommentRemove $event)
	{
        if(!$event->userId || !$event->articleId || !$event->commentId) return true;

        \App\Article::where('id', $event->articleId)->update([
            'comment_num' =>  \App\ArticleComment::where('article_id', $event->articleId)->count(),
        ]);
	}

}
