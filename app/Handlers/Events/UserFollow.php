<?php namespace App\Handlers\Events;

//use App\Events\UserFollow;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserFollow {

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
	 * @param  UserFollow  $event
	 * @return void
	 */
	public function handle(\App\Events\UserFollow $event)
	{
        if(!$event->followerId || !$event->followedId) return true;
        \App\User::where('id', $event->followerId)->update([
            'follow_num' => \App\UserFollower::where('follower_id', $event->followerId)->count(),
        ]);
        \App\User::where('id', $event->followedId)->update([
            'fans_num' => \App\UserFollower::where('user_id', $event->followedId)->count(),
        ]);
        \App\Notification::create([
            'user_id' => $event->followedId,
            'type'    => config('shilehui.notification_type.follow'),
            'asso_id' => $event->followerId,
            'payload' => [ 'datetime' => $event->params['datetime'] ],
        ]);
	}

}
