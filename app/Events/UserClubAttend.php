<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;

class UserClubAttend extends Event {

	use SerializesModels;

    public $clubId, $userId;
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($userId, $clubId)
	{
        $this->userId = $userId;
        $this->clubId = $clubId;
	}

}
