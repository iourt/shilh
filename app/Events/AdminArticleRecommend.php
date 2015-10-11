<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;

class AdminArticleRecommend extends Event {

	use SerializesModels;

    public $destination, $articleId, $userId;
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($destination, $articleId, $userId,  $params=[])
	{
        $this->destination = $destination;
        $this->articleId   = $articleId;
        $this->userId      = $userId;
        $this->params      = $params;
	}

}
