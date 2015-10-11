<?php namespace App\Handlers\Events;

//use App\Events\UserArticlePraiseAdd;

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
	public function handle(\App\Events\UserArticlePraiseAdd $event)
	{
        if(!$event->userId || !$event->articleId || !$event->praiseId) return true;

        \App\Article::where('id', $event->articleId)->update([
            'praise_num' =>  \App\ArticlePraise::where('article_id', $event->articleId)->count(),
        ]);
        $article = \App\Article::with('images')->where('id', $event->articleId)->first();
        \App\Notification::create([
            'user_id' => $article->user_id,
            'type'    => config('shilehui.notification_type.praise'),
            'asso_id' => $event->praiseId,
            'payload' => [
                'article_title'     => $article->images[0]->brief,
                'article_image_url' => $article->images[0]->url,
            ],
        ]);

	}

}
