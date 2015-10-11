<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;

class UserArticlePraiseAdd extends Event {

	use SerializesModels;

    public $articleId, $userId, $praiseId;
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($articleId, $userId, $praiseId)
	{
        $this->articleId = $articleId;
        $this->userId    = $userId;
        $this->praiseId  = $praiseId;
	}

}
