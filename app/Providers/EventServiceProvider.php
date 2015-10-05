<?php namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		'event.name' => [
			'EventListener',
        ],
        // events made by user
        'App\Events\UserArticlePost' => [
            'App\Handlers\Events\UserArticlePost',
        ],
        'App\Events\UserArticlePraiseAdd' => [
            'App\Handlers\Events\UserArticlePraiseAdd',
        ],
        'App\Events\UserArticlePraiseRemove' => [
            'App\Handlers\Events\UserArticlePraiseRemove',
        ],
        'App\Events\UserArticleCollectionAdd' => [
            'App\Handlers\Events\UserArticleCollectionAdd',
        ],
        'App\Events\UserArticleCollectionRemove' => [
            'App\Handlers\Events\UserArticleCollectionRemove',
        ],
        'App\Events\UserClubJoin' => [
            'App\Handlers\Events\UserClubJoin',
        ],
        'App\Events\UserClubExit' => [
            'App\Handlers\Events\UserClubExit',
        ],
        'App\Events\UserFollow' => [
            'App\Handlers\Events\UserFollow',
        ],
        'App\Events\UserUnFollow' => [
            'App\Handlers\Events\UserUnFollow',
        ],
        'App\Events\UserArticleCommentAdd' => [
            'App\Handlers\Events\UserArticleCommentAdd',
        ],
        'App\Events\UserArticleCommentRemove' => [
            'App\Handlers\Events\UserArticleCommentRemove',
        ],
        'App\Events\UserReg' => [
            'App\Handlers\Events\UserReg',
        ],
        'App\Events\UserChat' => [
            'App\Handlers\Events\UserChat',
        ],
        // event produced by admin
	];

	/**
	 * Register any other events for your application.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		parent::boot($events);

		//
	}

}
