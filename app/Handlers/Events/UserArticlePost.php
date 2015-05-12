<?php namespace App\Handlers\Events;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UserArticlePost {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
    protected $article;
    protected $author;
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  UserArticlePost  $event
	 * @return void
	 */
	public function handle(\App\Events\UserArticlePost $event)
	{
        $articleTypes = config('shilehui.article_type');
        $this->article = \App\Article::find($event->articleId);
        $this->author  = \App\User::find($this->article->user_id);
        if(empty($this->article) || empty($this->author)) return;

        switch($event->articleType){
            case $articleTypes['normal']: $this->handleForNormal();break;
            case $articleTypes['club']:   $this->handleForClub();break;
            case $articleTypes['activity']: $this->handleForActivity();break;
        }
	}
    public function handleForNormal(){
        $this->_updateArticleNumOfAuthor();
        $this->_updateArticleNumOfCategory();
        $this->_updateUserRecentCategory();
    }
    public function handlerForClub(){
        $this->_updateArticleNumOfAuthor();
        $this->_updateArticleNumOfClub();
        $this->_updateArticleNumOfCategory();
        $this->_updateUserRecentCategory();
        $this->_updateUserRecentClub();
    }
    public function handleForActivity(){
    }

    private function _updateArticleNumOfArticle(){
        $this->author->article_num +=1;
        $this->author->save();
    }
    private function _updateArticleNumOfClub(){
        $club = \App\Club::find($this->params['Club']);
        if(empty($club)) return;
        $club->article_num +=1;
        $alub->save();
    }
    private function _updateArticleNumOfCategory(){
        $category = \App\Category::find($this->article->category_id);
        if(empty($category)) return;
        $category->article_num +=1;
        $category->save();
    }
    private function _updateArticleNumOfActivity(){
        $activity = \App\Activity::find($this->params['activity_id']);
        if(empty($activity)) return;
        $activity->article_num +=1;
        $activity->save();
    }
    private function _updateUserRecentCategory(){
        $types = config('shilehui.user_recent_types');
        $item = \App\UserRecentUpdate::firstOrNew(['user_id' => $this->article->user_id, 'type' =>$types['category'], 'type_id' => $this->article->category_id]);

        $item->article_id = $this->article->id;
        $item->save();
    }
    private function _updateUserRecentCategory(){
        $types = config('shilehui.user_recent_types');
        $item = \App\UserRecentUpdate::firstOrNew(['user_id' => $this->article->user_id, 'type' =>$types['club'], 'type_id' => $this->params['club_id'] ]);
        $item->article_id = $this->article->id;
        $item->save();
    }

}
