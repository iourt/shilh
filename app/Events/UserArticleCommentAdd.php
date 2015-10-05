<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;

class UserArticleCommentAdd extends Event {

	use SerializesModels;
    public $userId, $articleId, $commentId;
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($userId, $articleId, $commentId, $params=[])
	{
        $this->userId = $userId;
        $this->articleId = $articleId;
        $this->commentId = $commentId;
        $this->params    = $params;
	}

}
