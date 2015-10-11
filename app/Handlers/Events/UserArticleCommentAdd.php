<?php namespace App\Handlers\Events;

//use App\Events\UserArticleCommentAdd;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserArticleCommentAdd {

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
	 * @param  UserArticleCommentAdd  $event
	 * @return void
	 */
	public function handle(\App\Events\UserArticleCommentAdd $event)
	{
        if(!$event->userId || !$event->articleId || !$event->commentId) return true;

        \App\Article::where('id', $event->articleId)->update([
            'comment_num' =>  \App\ArticleComment::where('article_id', $event->articleId)->count(),
        ]);
        $article = \App\Article::with('images')->where('id', $event->articleId)->first();
        \App\Notification::create([
            'user_id' => $article->user_id,
            'type'    => config('shilehui.notification_type.comment'),
            'asso_id' => $event->commentId,
            'payload' => [
                'article_title'     => $article->images[0]->brief,
                'article_image_url' => $article->images[0]->url,
            ],
        ]);
	}

}
