<?php namespace App\Events;

use App\Events\Event;

use Illuminate\Queue\SerializesModels;

class UserArticleCollectionAdd extends Event {

	use SerializesModels;

    public $articleId, $userId, $collectionId;
	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($articleId, $userId, $collectionId)
	{
        $this->articleId = $articleId;
        $this->userId    = $userId;
        $this->collectionId = $collectionId;
	}

}
