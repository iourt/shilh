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
        $this->params      = $event->params;
        $this->articleType = $event->articleType;
        $this->article     = \App\Article::find($event->articleId);
        $this->author      = \App\User::find($this->article->user_id);
        if(empty($this->article) || empty($this->author)) return;
        if( $event->articleType == $articleTypes['normal']){
            $this->_updateArticleNumOfAuthor();
            $this->_updateArticleNumOfCategory();
            $this->_updateUserRecentCategory();
            $this->_updateUserExp();
            $this->_makeArticleThumb();
        }
        if( $event->articleType == $articleTypes['club']){
            $this->_updateArticleNumOfAuthor();
            $this->_updateArticleNumOfClub();
            $this->_updateArticleNumOfCategory();
            $this->_updateUserRecentCategory();
            $this->_updateUserRecentClub();
            $this->_updateUserExp();
            $this->_makeArticleThumb();
        }
        if( $event->articleType == $articleTypes['activity']){
            $this->_updateArticleNumOfAuthor();
            $this->_updateArticleNumOfActivity();
            $this->_updateArticleNumOfCategory();
            $this->_updateUserRecentCategory();
            $this->_updateUserRecentActivity();
            $this->_updateUserExp();
            $this->_makeArticleThumb();
        }

	}

    private function _updateArticleNumOfAuthor(){
        $this->author->article_num +=1;
        $this->author->save();
    }
    private function _updateArticleNumOfClub(){
        $club = \App\Club::find($this->article->club_id);
        if(empty($club)) return;
        $club->article_num +=1;
        $club->article_updated_at = \Carbon\Carbon::now();
        $club->save();
        $ca = \App\ClubArticle::firstOrNew(['article_id' => $this->article->id, 'club_id' => $this->article->club_id]);
        $ca->save();
    }
    private function _updateArticleNumOfCategory(){
        $category = \App\Category::find($this->article->category_id);
        if(empty($category)) return;
        $category->article_num +=1;
        $category->save();
    }
    private function _updateArticleNumOfActivity(){
        $activity = \App\Activity::find($this->article->activity_id);
        if(empty($activity)) return;
        $activity->article_num +=1;
        $activity->save();
    }
    private function _updateUserRecentCategory(){
        return true;
        $type = config('shilehui.user_update_type.club');
        $item = \App\UserRecentUpdate::firstOrNew([
            'user_id' => $this->article->user_id, 
            'type'    => config('shilehui.user_recent_type.club'), 
            'type_id' => $this->params['club_id'],
            'article_id' => $this->article->id,
        ]);
        $item->save();
    }
    private function _updateUserRecentClub(){
        return true;
        $type = config('shilehui.user_update_type.club');
        $item = \App\UserRecentUpdate::firstOrNew([
            'user_id' => $this->article->user_id, 
            'type'    => config('shilehui.user_recent_type.club'), 
            'type_id' => $this->params['club_id'],
            'article_id' => $this->article->id,
        ]);
        $item->save();
    }
    private function _updateUserRecentActivity(){
        return true;
    }
    private function _updateUserExp(){
        $uniqId = sprintf("%s:%s", config('shilehui.exp_action.by_self.post.id'), $this->article->id);
        $ueLog = \App\UserExpLog::firstOrNew([ 'uniq_id' => $uniqId, 'user_id' => $this->author->id]);
        $ueLog->fill([
            'action'  => config('shilehui.exp_action.by_self.post.id'),
            'exp'     => config('shilehui.exp_action.by_self.post.exp'),
            'data'    => [ 'article_id' => $this->article->id ],
        ]);
        if($ueLog->id){
            return;
        }
        $ueLog->save();
        \App\Lib\User::updateExp($this->author->id, config('shilehui.exp_action.by_self.post.exp'));
    }

    private function _makeArticleThumb(){
        $arr = $this->article->images;
        foreach($arr as $img){
            if($img->width <= config('shilehui.dimension.article_thumb_width')){
                $img->thumb_width  = $img->width;
                $img->thumb_height = $img->height;
            } else {
                $img->thumb_width  = config('shilehui.dimension.article_thumb_width');
                $img->thumb_height = $img->height * $img->thumb_width/$img->width;
            }
            $img->save();
        }
    }

}
