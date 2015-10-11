<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class TTest extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'TTest:event';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
//        event(new \App\Events\UserArticlePost($article->id, $articleType, [])); 
        event(new \App\Events\UserArticleCollectionAdd($articleId=1, $userId=1, $collectionId=3));
        event(new \App\Events\UserArticleCollectionRemove($articleId=1, $userId=1, $collectiionId=3));
        event(new \App\Events\UserArticleCommentAdd($articleId=1, $userId=1, $commentId=3));
        event(new \App\Events\UserArticleCommentAdd($articleId=1, $userId=1, $commentId=4));
        event(new \App\Events\UserArticleCommentRemove($articleId=1, $userId=1, $commentId=3));
        event(new \App\Events\UserArticlePost($articleId=2, $articleType=config('shilehui.article_type.normal'), $params=[]));
        event(new \App\Events\UserArticlePost($articleId=3, $articleType=config('shilehui.article_type.club'), $params=[]));
        event(new \App\Events\UserArticlePost($articleId=4, $articleType=config('shilehui.article_type.activity'), $params=[]));
        event(new \App\Events\UserArticlePost($articleId=5, $articleType=config('shilehui.article_type.subject'), $params=[]));
        event(new \App\Events\UserArticlePraiseAdd($articleId=1, $userId=1, $praiseId=1));
        event(new \App\Events\UserArticlePraiseRemove($articleId=1, $userId=1, $praiseId=1));
        event(new \App\Events\UserClubExit($clubId=1, $userId=1));
        event(new \App\Events\UserClubJoin($clubId=1, $userId=1));
        event(new \App\Events\UserFollow($followedId=1, $followingId=2, [ 'datetime' => \Carbon\Carbon::now()->toDatetimeString() ]));
        event(new \App\Events\UserUnFollow($followedId=1, $followingId=2,  [ 'datetime' => \Carbon\Carbon::now()->toDatetimeString() ]));
        event(new \App\Events\UserFollow($followedId=1, $followingId=2,  [ 'datetime' => \Carbon\Carbon::now()->toDatetimeString() ]));
        event(new \App\Events\UserUnFollow($followedId=1, $followingId=2,  [ 'datetime' => \Carbon\Carbon::now()->toDatetimeString() ]));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			//['example', InputArgument::REQUIRED, 'An example argument.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			//['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
		];
	}

}
