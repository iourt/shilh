<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;

class UserArticlePost extends Event {

	use SerializesModels;

    public $articleId, $articleType, $params;
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($articleId, $articleType, $params)
	{
        $this->articleId = $articleId;
        $this->articleType = $articleType;
        $this->params = $params;
	}

}
