<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;

class UserArticlePraiseRemove extends Event {

	use SerializesModels;

    public $articleId, $userId;
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($articleId, $userId)
	{
        $this->articleId = $articleId;
        $this->userId    = $userId;
	}

}
