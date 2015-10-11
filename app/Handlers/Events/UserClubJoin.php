<?php namespace App\Handlers\Events;


use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserClubJoin {

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
	 * @param  UserClubJoin  $event
	 * @return void
	 */
	public function handle(\App\Events\UserClubJoin $event)
	{
        if(!$event->userId || !$event->clubId) return true;

        //\App\Club::where('id', $event->clubId)->increment('user_num');
        \App\Club::where('id', $event->clubId)->update([
            'user_num' =>  \App\ClubUser::where('club_id', $event->clubId)->where('has_exited', 0)->count(),
        ]);
        //\App\User::where('id', $event->userId)->increment('club_num');
        \App\User::where('id', $event->userId)->update([
            'club_num' => \App\ClubUser::where('user_id', $event->userId)->where('has_exited', 0)->count(),
        ]);
	}

}
