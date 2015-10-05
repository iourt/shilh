<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;

class UserUnFollow extends Event {

	use SerializesModels;

    public $followedId, $followerId;
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($followedId, $followerId)
	{
        $this->followedId = $followedId;
        $this->followerId = $followerId;
	}

}
