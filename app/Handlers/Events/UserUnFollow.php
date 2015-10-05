<?php namespace App\Handlers\Events;

//use App\Events\UserUnFollow;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserUnFollow {

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
	 * @param  UserUnFollow  $event
	 * @return void
	 */
	public function handle(\App\Events\UserUnFollow $event)
	{
        if(!$event->followerId || !$event->followedId) return true;
        \App\User::where('id', $event->followerId)->update([
            'follow_num' => \App\UserFollow::where('follower_id', $event->followerId)->count(),
        ]);
        \App\User::where('id', $event->followedId)->update([
            'fans_num' => \App\UserFollow::where('user_id', $event->followedId)->count(),
        ]);
	}

}
