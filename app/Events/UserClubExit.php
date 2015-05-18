<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;

class UserClubExit extends Event {

	use SerializesModels;

    public $clubId, $userId;
	public function __construct($clubId, $userId)
	{
        $this->clubId = $clubId;
        $this->userId = $userId;
	}

}
