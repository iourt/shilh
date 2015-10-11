<?php namespace App\Handlers\Events;

//use App\Events\AdminArticleRecommend;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class AdminArticleRecommend {

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
	 * @param  AdminArticleRecommend  $event
	 * @return void
	 */
	public function handle(\App\Events\AdminArticleRecommend $event)
	{
        $uniqId = sprintf("%s:%s", config('shilehui.exp_action.by_admin.recommend.id'), $event->articleId);
        $ueLog = \App\UserExpLog::firstOrNew([ 'uniq_id' => $uniqId, 'user_id' => $event->userId ]);
        $ueLog->fill([
            'action'  => config('shilehui.exp_action.by_admin.recommend.id'),
            'exp'     => config('shilehui.exp_action.by_admin.recommend.exp'),
            'data'    => [ 'article_id' => $event->articleId ],
            ]);
        //if($ueLog->id){
        //    return;
       // }
        $ueLog->save();
        \App\Lib\User::updateExp($event->userId, config('shilehui.exp_action.by_admin.recommend.exp'));
	}

}
