<?php namespace App\Handlers\Events;

//use App\Events\UserArticleCollectionAdd;

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
	public function handle(\App\Events\UserArticleCollectionAdd $event)
	{
        if(!$event->userId || !$event->articleId || !$event->collectionId) return true;

        \App\Article::where('id', $event->articleId)->update([
            'collection_num' =>  \App\ArticleCollection::where('article_id', $event->articleId)->count(),
        ]);
        \App\User::where('id', $event->userId)->update([
            'collection_num' => \App\ArticleCollection::where('user_id', $event->userId)->count(),
        ]);
        $article = \App\Article::with('images')->where('id', $event->articleId)->first();
        \App\Notification::create([
            'user_id' => $article->user_id,
            'type'    => config('shilehui.notification_type.collection'),
            'asso_id' => $event->collectionId,
            'payload' => [
                'article_title'     => $article->images[0]->brief,
                'article_image_url' => $article->images[0]->url,
            ],
        ]);
	}

}
