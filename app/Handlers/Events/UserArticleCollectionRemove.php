<?php namespace App\Handlers\Events;

//use App\Events\UserArticleCollectionRemove;

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
	public function handle(\App\Events\UserArticleCollectionRemove $event)
	{
        if(!$event->userId || !$event->articleId || !$event->collectionId) return true;

        \App\Article::where('id', $event->articleId)->update([
            'collection_num' =>  \App\ArticleCollection::where('article_id', $event->articleId)->count(),
        ]);
        \App\User::where('id', $event->userId)->update([
            'collection_num' => \App\ArticleCollection::where('user_id', $event->userId)->count(),
        ]);
	}

}
