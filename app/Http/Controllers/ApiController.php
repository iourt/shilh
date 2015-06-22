<?php namespace App\Http\Controllers;

//use App\Http\Requests;
//use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use App\Http\Requests\CRequest AS Request; 

class ApiController extends Controller {
    protected $output;
	public function __construct() {
        $this->output = [];;
	}
    private function _render(Request $request, $ack = true){
        $this->output['Response'] = ['Time' => time(), 'State' => $request->crIsUserLogin(), 'Ack' => $ack ? 'Success' : 'Failure'];
        return response()->json($this->output);
    } 
    private function _validate($request, $rules){
        $v = \Validator::make($request->all(), $rules);
        if($v->fails()){
            $this->output['Response'] = ['Time' => time(), 'State' => $request->crIsUserLogin(), 'Ack' => 'Failure'];
            throw new \App\Exceptions\ApiException($this->output, 200); 
        }
    }
    public function getUserInfo(Request $request){
        $this->_validate($request, [
            'UserId' => 'required|exists:users,id',
            ]);
        $isViewMine = $request->input('UserId') == $request->crUserId();
        $user = \App\User::find($request->input('UserId'));
        if(empty($user)){
            \Log::info("user is empty");
            return $this->_render($request,false);
        }
        $needRefreshUserStat = true;//TODO set it to false when production env
        $stat = \App\Lib\User::getUserStat($user->id, $needRefreshUserStat);
        $this->output = [
            'UserImage' => url($user->avatar->url),
            'Username'  => $user->name,
            'Exper'     => $user->exp_num,
            'RankName'  => '',
            'TotalFollow' => $user->follow_num,
            'TotalFans'   => $user->fans_num,
            'ArticleList' => [],
            'CollectList' => [],
            'ClubList'    => [],
            'TotalCollect' => $user->collection_num,
            'TotalArticle' => $user->article_num,
            'TotalClub'    => $user->club_num,
            ];
        foreach($stat['latest_article_category'] as $c){
            $this->output['ArticleList'][] = [
                'CateId'   => $c->id,
                'ImageUrl' => url($c->cover_image->url),
                'CateName' => $c->name,
                'TotalArticle' => $c->article_num,
                'TotalPraise'  => $c->praise_num,
            ];
        }
        foreach($stat['latest_collection_category'] as $c){
            $this->output['CollectList'][] = [
                'CateId'   => $c->id,
                'ImageUrl' => url($c->cover_image->url),
                'CateName' => $c->name,
                'TotalArticle' => $c->article_num,
                'TotalPraise'  => $c->praise_num,
            ];
        }
        foreach($stat['latest_club'] as $c){
            $this->output['ClubList'][] = [
                'ClubId'    => $c->id,
                'ImageUrl'  => url($c->cover_image->url),
                'ClubName'  => $c->name,
                'TodayNews' => $c->today_article_num,
            ];
        }
        return $this->_render($request);
    }
    public function index(Request $request){
        return $this->_render($request);
    }
    public function getLogin(Request $request){
        $this->_validate($request, [
            'Phone'    => 'required|numeric',
            'Password' => 'required',
            ]);

        $user = \App\User::where('mobile', $request->get('Phone'))->first();
        if(empty($user)){
            return $this->_render($request,false);
        }
        $password = $request->get('Password');
        $encryptPass = \App\Lib\Auth::encryptPassword($password, $user->salt);
        if($user->encrypt_pass != $encryptPass) {
            \Log::info("password fail [$encryptPass][".$user->encrypt_pass."]"); 
            return $this->_render($request,false);
        }
        $user->challenge_id = time();
        $user->save();
        $auth = new \App\Lib\Auth('API', $user->id);
        $sessUser = $auth->setUserAuth();
        $this->output = ['UserId' => $user->id, 'Auth' => $sessUser['auth']];
        return $this->_render($request);
    }
    public function getLogout(Request $request) {
        $auth = new \App\Lib\Auth('API', $request->crUserId());
        if($request->crIsUserLogin()){
            $auth->removeUserAuth();
            return $this->_render($request); 
        } else {
            return $this->_render($request, false); 
        }
    }
    public function setRegInfo(Request $request) {
        $this->_validate($request, [
            'UserName'    => 'required|string|min:2,max:32',
            'Sex'         => 'required|in:'.implode(",", config('shilehui.sex')),
            'Area'        => 'required|exists:areas,id',
            'Job'         => 'required|exists:jobs,id',
            'Phone'       => 'required|',
            'Password'    => 'required',
            ]);
        $user = \App\User::where('mobile', $request->input('Phone'))->first();
        if($user) {
            return $this->_render($request,false);
        }
        $salt = rand(100000,999999);
        //$user = \App\User::firstOrNew(['mobile', $request->input('Phone')]);
        $user = new \App\User;
        $user->mobile = $request->input('Phone');
        $user->sex = $request->input('Sex');
        $user->area_id = $request->input('Area');
        $user->job_id = $request->input('Job');
        $user->name = $request->input('UserName');
        $user->salt  =$salt;
        $user->chanllenge_id=time();
        $user->encrypt_pass = \App\Lib\Auth::encryptPassword($request->input('Password'), $salt);
        $res = $user->save();
        $this->output = ['UserId'=>$user->id ];
        return $this->_render($request);
    }
    public function getCityList_1(Request $request) {
        $list = \App\Lib\Area::all();
        $this->output = $list;
        return $this->_render($request);
    }
    public function setArticle(Request $request) {
        $this->_validate($request, [
            'Title' => 'required|string|min:5,max:256',
            'Category' => 'required|exists:categories,id',
            'Images' => 'required|array',
            'Club' => 'exists:clubs,id',
            'Activity' => 'exists:activities,id',
            ]);
        $articleTypes = config('shilehui.article_type');
        if($request->input('Club')) {
            $articleType = $articleTypes['club'];
        } else if($request->input('Activity')){
            $articleType = $articleTypes['activity'];
        } else {
            $articleType = $articleTypes['normal'];
        }
        $hasCommitTransaction = false;
        try{
        \DB::beginTransaction();
        $article = new \App\Article;
        $article->title = $request->input('Title');
        $article->category_id = $request->input('Category');
        $article->user_id = $request->crUserId();
        $article->save();
        foreach($request->input('Images') as $image){
            if(strlen($image['File']) < 100 ) continue;
            $imageData    = \App\Lib\Image::decodeAndSaveAsTmp($image['File'], $request->crUserId());
            $articleImage = new \App\ArticleImage;
            $articleImage->article_id  = $article->id;
            $articleImage->brief       = $image['Brief'];
            $articleImage->width       = $imageData['width'];
            $articleImage->height      = $imageData['height'];
            $articleImage->filename    = $imageData['name'];
            $articleImage->ext         = $imageData['ext'];
            $articleImage->size        = $imageData['size'];
            $articleImage->save();
        }
        $hasCommitTransaction = true;
        } catch (Exception $e){
            $hasCommitTransaction = false;
            \DB::rollback();
        }
        if(!$hasCommitTransaction){
            return $this->_render($request,false);
        }
        $images = $article->images;
        foreach($images as $image){
            \App\Lib\Image::moveToDestination($image->filename, $image->ext);
        }
        event(new \App\Events\UserArticlePost($article->id, $articleType, [])); 
        return $this->_render($request);
    }

    function getListArticle(Request $request){
        $this->_validate($request, [
            'CateId'     => 'exists:categories,id',
            'SubjectId'  => 'exists:subjects,id',
            'ClubId'     => 'exists:clubs,id',
            'ActivityId' => 'exists:activities,id',
            'UserId'     => 'exists:users,id',
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
            ]);
        $query = null;
        if($request->input('CateId')){
            $query = \App\Article::whereIn('id', function($query) use ($request) { 
                $query->select('article_id')->from(with(new \App\CategoryArticle)->getTable())
                    ->where('category_id', $request->input('CateId'));
            });
        } else if($request->input('SubjectId')) {
            $query = \App\Article::whereIn('id', function($query) use ($request) { 
                $query->select('article_id')->from(with(new \App\SubjectArticle)->getTable())
                    ->where('subject_id', $request->input('SubjectId'));
            });
        } else if($request->input('ClubId')) {
            $query = \App\Article::whereIn('id',  function($query) use ($request) { 
                $query->select('article_id')->from(with(new \App\ClubArticle)->getTable())
                    ->where('club_id', $request->input('ClubId'));
            });
        } else if($request->input('ActivityId')) {
            $query = \App\Article::whereIn('id', function($query) use ($request) { 
                $query->select('article_id')->from(with(new \App\ActivityArticle)->getTable())
                    ->where('activity_id', $request->input('ActivityId'));
            });
        } else if($request->input('UserId')) {
            $query = \App\Article::where('user_id', $request->input('UserId')); 
        } else {
            return $this->_render($request);
        }
        $total = $query->count();
        $articles = $query->with('images','user'. 'user.avatar')->skip( ($request->input('PageIndex') - 1)*$request->input('PageSize'))->take($request->input('PageSize'))->get();
        $this->output = ['ArticleList' => [], 'Total' => $total ];
        foreach($articles as $article){
            $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 'Images' => [], 'Author' => [], 'CategoryList' => [] ];
            foreach($article->images as $image){
                $item['Images'][]=['ImageUrl' => url($image->url), 'Description' => $image->brief, 'Width' => $image->thumb_width, 'Height' => $image->thumb_height ]; 
            }
            $item['Author']['UserId']   = $article->user_id;
            $item['Author']['ImageUrl'] = url($article->user->avatar->url);
            $item['Author']['UserName'] = $article->user->name;
            $item['CategoryList'] = \App\Lib\Category::renderBreadcrumb($article->category_id);
            $this->output['ArticleList'][]=$item;
        }
        //var_dump(\DB::getQueryLog());
        return $this->_render($request);



    }



    public function getContentArticle(Request $request){
        $this->_validate($request, [
            'ArticleId'     => 'exists:articles,id',
            ]);
        
        $article = \App\Article::find($request->input('ArticleId'));


        $this->output = [
            'ArticleId'   => $article->id, 
            'UpdatedTime' => $article->user_updated_at->toDateTimeString(),
            'CreatedTime' => $article->created_at->toDateTimeString(),
            'Total' => [
                'TotalHit'     => $article->view_num, 
                'StatePraise'  => $article->is_praised_by_user($request->crUserId()) ? 2 : 1,
                'TotalPraise'  => $article->praise_num,
                'StateCollect' => $article->is_collected_by_user($request->crUserId()) ? 2 : 1,
                'TotalShare'   => 0,
                'TotalCOmment' => $article->comment_num,
            ],
            'Affect' => [
                'ClubId'       => 0, 
                'ClubName'     => '', 
                'SubjectId'    => 0, 
                'SubjectName'  => '', 
                'ActivityId'   => 0, 
                'ActivityName' => '', 
            ],
            'Images' => [], 
            'Author' => [], 
            'CategoryList' => [],
            'PraiseUser'   => [],
            'EditState'    => $article->is_shown_in_category ? 2 : 1,
        ];
        foreach($article->images as $image){
            $this->output['Images'][]=['ImageUrl' => url($image->url), 'Description' => $image->brief, 'Width' => $image->thumb_width, 'Height' => $image->thumb_height ]; 
        }
        if($article->club){
            $this->output['Affect']['ClubId']   = $article->club->id;
            $this->output['Affect']['ClubName'] = $article->club->name;
        }
        if($article->subject){
            $this->output['Affect']['SubjectId']   = $article->subject->id;
            $this->output['Affect']['SubjectName'] = $article->subject->name;
        }
        if($article->activity){
            $this->output['Affect']['ActivityId']   = $article->activity->id;
            $this->output['Affect']['ActivityName'] = $article->activity->name;
        }


        $this->output['Author']['UserId']   = $article->user->id;
        $this->output['Author']['ImageUrl'] = url($article->user->avatar->url);
        $this->output['Author']['UserName'] = $article->user->name;
        $this->output['CategoryList']  = \App\Lib\Category::renderBreadcrumb($article->category_id);
        $praiseUsers = \App\ArticlePraise::with('user')->where('article_id', $article->id)->take(10)->get();
        foreach($praiseUsers as $pu){
            $this->output['PraiseUser'][] = ['UserId' => $pu->user_id, 'UserName' => $pu->user->name, 'ImageUrl' => url($pu->user->avatar->url)];
        }

        return $this->_render($request);

    }

    public function getCityList(Request $request){
        $areas = \App\Lib\Area::all();
        $this->output=['List' => []];
        foreach($areas['province'] as $p){
            $arrP = ['Id' => $p['id'], 'Name' => $p['name'], 'Child' => [] ];
            foreach($areas['city'] as $c){
                if($c['province_id'] != $p['id']) continue;
                $arrC = ['Id' => $c['id'], 'Name' => $c['name'], 'Child' => [] ];
                foreach($areas['county'] as $n){
                    if($n['city_id'] != $c['id']) continue;
                    $arrC['Child'][] = ['Id' => $n['id'], 'Name' => $n['name']];
                }
                $arrP['Child'][] = $arrC;
            }
            $this->output['List'][] = $arrP;
        }
        return $this->_render($request);

    }

    public function getListCategory(Request $request){
        $scopeTypes = ['child' => 1, 'descendant' => 2, 'brother' =>3]; 
        $this->_validate($request, [
            'CateId'     => 'exists:categories,id',
            'CateType'   => 'required|in:'.implode(",", array_values($scopeTypes)),
            ]);
        $cateId = $request->input('CateId', 0);
        $scopeType = $request->input('CateType');

        //Todo
        return $this->_render($request);
    
    }

    public function setReportArticle(Request $request){
        $this->_validate($request, [
            'ArticleId'  => 'exists:articles,id',
            'Contact'    => 'required',
            'ReportReason' => 'required',
            ]);
        $ar = new \App\ArticleReport();
        $ar->article_id = $request->input('ArticleId');
        $ar->contact    = $request->input('Contact');
        $ar->reason     = $request->input('ReportReason');
        $ar->user_id    = $request->crUserId();
        $ar->save();
        return $this->_render($request);

    }


    public function getListClub(Request $request){
        $sortTypes = ['todayPosts' => 1, 'id' => 2, 'postTimeDesc' => 3, 'postTimeAsc' => 4, 'letter' => 5]; 
        $this->_validate($request, [
            'SortType'  => 'required|in:'.implode(",", array_values($sortTypes)),
            'UserId'     => 'exists:users,id',
            ]);
        $query = with(new \App\Club)->with('cover_image');
        if($request->input('UserId')){
            $query = $query->whereIn('id', function($q) use($request){
                $q->select('club_id')->from(with(new \App\ClubUser)->getTable())->where('user_id', $request->input('UserId') );
            });
        }
        if($request->input('SortType') == $sortTypes['todayPosts']){
            $query = $query->orderBy('article_num', 'desc')->take(5);
        } else if($request->input('SortType') == $sortTypes['id']){
            $query = $query->orderBy('id', 'desc');
        } else {
            return $this->_render($request,false);
        }
        $clubs = $query->get();
        $this->output=['ClubList'=>[]];
        foreach($clubs as $c){
            
            $this->output['ClubList'][] = [
                'ClubId'       => $c->id,
                'ClubName'     => $c->name,
                'ImageUrl'     => url($c->cover_image->url),
                'Description'  => $c->brief,
                'TotalUser'    => $c->user_num,
                'TotalArticle' => $c->article_num,
                'Letter'       => $c->letter,
                'UpdateTime'   => $c->updated_at->toDateTimeString(),
                'CreateTime'   => $c->created_at->toDateTimeString(),
                'CategoryList' => \App\Lib\Category::renderBreadcrumb($c->category_id),
                ];
        }
        return $this->_render($request);

    }

    public function getContentClub(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'exists:clubs,id',
            ]);
        
        $club       = \App\Club::find($request->input('ClubId'));
        $clubUser   = \App\ClubUser::where('user_id', $request->crUserId())->where('club_id', $club->id)->first();
        $attendance = \App\Lib\UserClubAttendance::infoAt($request->crUserId(), $club->id, \Carbon\Carbon::now());
        $this->output=[
            'ClubId'      => $club->id,
            'ClubName'    => $club->name,
            'Description' => $club->brief,
            'ImageUrl'    => url($club->cover_image->url),
            'TotalMember' => $club->user_num,
            'TotalSign'   => $attendance->continuous_days,
            'TotalAlwaysSign' => $attendance->total_days,
            'StateJoin'       => empty($clubUser) ? false : true,
            'StateSign'       => $attendance->has_attended,
            'ActivityList'    => [
                'ActivityId'   => $club->activity->id,
                'ActivityName' => $club->activity->name,
                'ActivityType' => $club->activity-type,
            ],
            'ArticleTop'     => [ ],
            'CategoryList'    => \App\Lib\Category::renderBreadcrumb($club->category_id),
        ];
        $arr = \App\Article::join('club_articles', 'articles.id', '=', 'club_articles.article_id')
            ->where('club_articles.club_id', $club->id)->select('articles.*')
            ->with('user', 'images')
            ->orderBy('articles.collect_num', 'desc')->take(20);
            
        foreach($arr as $article){
            $item = [
                'ArticleId' => $article->id,
                'Images'    => [],
                'CategoryList' => \App\Lib\Category::getBreadcrumb($article->category_id),
                'Author'    => [
                    'UserId'     => $article->user->id,
                    'ImageUrl'   => $article->user->avatar->url,
                    'UserName'   => $article->user->name,
                ],
                'TotalCollect' => $article->colloct_num,
                ];
            foreach($article->images as $image){
                $item['Images'][] = [
                    'Description' => $image->brief,
                    'Width'       => $image->width,
                    'Height'      => $image->height,
                    'ImageUrl'    => url($image->url),
                    ];
            }
            $this->output['ArticleTop'][] = $item;
        }

        return $this->_render($request);
    
    }

    public function setJoinClub(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'required|exists:clubs,id',
        ]);
        $clubUser = \App\ClubUser::firstOrNew(['user_id' =>  $request->crUserId(), 'club_id' => $request->input('ClubId')]);
        if($clubUser->id && !$clubUser->has_exited){
            return $this->_render($request);
        }
        $clubUser->has_exited = 0;
        if($clubUser->save()){
            event(new \App\Events\UserClubJoin($clubUser->club_id, $clubUser->user_id));
        }
        return $this->_render($request);
    
    }

    public function setLeaveClub(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'required|exists:clubs,id',
        ]);
        $clubUser = \App\ClubUser::where('user_id',  $request->crUserId())->where('club_id', $request->input('ClubId'))->first();
        if(!$clubUser || $clubUser->has_exited){
            return $this->_render($request);
        }
        $clubUser->has_exited = 1;
        if($clubUser->save()){
            event(new \App\Events\UserClubExit($clubUser->club_id, $clubUser->user_id));
        }
        return $this->_render($request);
    
    }

    public function getClubHotUser(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'required|exists:clubs,id',
        ]);
        $hotUsers = \App\ClubTopUser::with('\App\User')->where('club_id', $request->input('ClubId'))->orderBy('article_num', 'desc')->get();
        $this->output = ['UserList' => []];
        foreach($hotUsers as $hu){
            $this->output['UserList'][]=[
                'UserId'   => $hu->user->id,
                'UserName' => $hu->user->name,
                'ImageUrl' => url($hu->user->avatar->url),
            ];
        }

        return $this->_render($request);
    
    }

    public function setSignClub(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'required|exists:clubs,id',
        ]);
        $clubUser = \App\ClubUser::where('user_id',  $request->crUserId())->where('club_id', $request->input('ClubId'))->first();
        if(!$clubUser || $clubUser->has_exited){
            return $this->_render($request);
        }
        $today = \Carbon\Carbon::now();
        $attendance = \App\Lib\UserClubAttendance::infoAt( $request->crUserId(), $request->input('ClubId'), $today);
        if($attendance->has_attended){
            return $this->_render($request);
        }
        $todayAttendance = new \App\UserClubAttendance;
        $todayAttendance->user_id = $request->crUserId();
        $todayAttendance->club_id = $request->input('ClubId');
        $todayAttendance->attended_at = $today;
        $todayAttendance->days        = $attendance->continuous_days + 1;
        if($todayAttendance->save()){
            //event(new \App\Events\UserClubAttend($clubUser->club_id, $clubUser->user_id));
        }
        return $this->_render($request);
    
    }

    public function getListComment(Request $request){
        $sortTypes = ['timeDesc' => 1, 'timeAsc' => 2, 'idDesc' => 3, 'idAsc' => 4];
        $this->_validate($request, [
            'SortType'   => 'required|integer|in:'.implode(",", array_values($sortTypes)),
            'ArticleId'  => 'required|exists:articles,id',
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ]);

        $query = \App\ArticleComment::where('article_id', $request->input('ArticleId'));
        $total = $query->count();
        switch ($request->input('SortType')) {
        case $sortTypes['timeDesc']:
        case $sortTypes['idDesc']:
            $query = $query->orderBy('id', 'desc');
            break;
        case $sortTypes['timeAsc']:
        case $sortTypes['idAsc']:
            $query = $query->orderBy('id', 'asc');
            break;
        }
        $comments = $query->with('user')->take($request->input('PageSize'))->skip( ($request->input('PageIndex')-1)*$request->input('PageSize'))->get();
        $this->output = ['CommentList' => [], 'Total' => $total];
        foreach($comments as $c){
            $this->output['CommentList'][] = [
                'CommentId' => $c->id,
                'ArticleId' => $c->article_id,
                'Author'    => [
                    'UserId'   => $c->user->id,
                    'ImageUrl' => url($c->user->avatar->url),
                    'UserName' => $c->user->name,
                ],
                'UpdateTime' => $c->updated_at->toDateTimeString(),
                'Content'    => $c->comment,
            ];
        }
        return $this->_render($request);
    }

    public function setArticleComment(Request $request){
        $this->_validate($request, [
            'ArticleId'  => 'required|exists:articles,id',
            'Content'    => 'required|string|min:5',
        ]);
        $comment = new \App\ArticleComment;
        $comment->article_id = $request->input('ArticleId');
        $comment->comment = $request->input('Content');
        $comment->user_id = $request->crUserId();
        $comment->save();
        return $this->_render($request);
    }

    public function getUserFans(Request $request){
        $this->_validate($request, [
            'UserId'  => 'required|exists:users,id',
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ]);
        $query = \App\UserFollower::where('user_id', $request->input('UserId'));
        $total = $query->count();
        $relations = $query->with('user')->take($request->input('PageSize'))->skip(($request->input('PageIndex')-1)*$request->input('PageSize'))->get();
        $this->output['FansList'] = [];
        foreach($relations as $r){
            $this->output['FansList'][]=[
                'UserId'    => $r->follower_id,
                'UserName'  => $r->follower->name,
                'UserImage' => url($r->follower->avatar->url),
                'State'     => $r->is_twoway ? 2 : 1,
            ];
        }
        $this->output['Total'] = $total;
        return $this->_render($request);
    }

    public function getUserFollow(Request $request){
        $this->_validate($request, [
            'UserId'  => 'required|exists:users,id',
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ]);
        
        $query = \App\UserFollower::where('follower_id', $request->input('UserId'));
        $total = $query->count();
        $relations = $query->with('user')->take($request->input('PageSize'))->skip(($request->input('PageIndex')-1)*$request->input('PageSize'))->get();
        $this->output = ['FollowList' => [] ];
        foreach($relations as $r){
            $this->output['FollowList'][]=[
                'UserId'    => $r->user_id,
                'UserName'  => $r->user->name,
                'UserImage' => url($r->user->avatar->url),
                'State'     => $r->is_twoway ? 2 : 1,
            ];
        }
        $this->output['Total'] = $total;
        return $this->_render($request);
    }

    public function getListActivity(Request $request){
        $this->_validate($request, [
            'ActivityType'  => 'required|in:'.implode(",", array_merge(array_values(config('shilehui.activity_type')),[0])),
            'PageIndex'     => 'required|integer',
            'PageSize'      => 'required|integer',
        ]);
        
        $query = with(new \App\Activity)->with('cover_image');
        if($request->input('ActivityType')!=0) {
            $query = $query->where('type', $request->input('ActivityType'));
        }
        $total = $query->count();
        $activities = $query->take($request->input('PageSize'))->skip(($request->input('PageIndex')-1)*$request->input('PageSize'))->get();
        $this->output = ['ActivityList' => [], 'Total' => $total];
        foreach($activities as $a){
            $this->output['ActivityList'][]=[
                'ActivityId'    => $a->id,
                'ActivityName'  => $a->name,
                'ActivityLabel'  => $a->alias,
                'ActivityTyp'    => $a->type,
                'ImageUrl'       => url($a->cover_image->url),
                'Description'    => $a->brief,
                'UpdateTime'     => $a->updated_at->toDateTimeString(),
                'CreatedTime'    => $a->created_at->toDateTimeString(),
            ];
        }
        return $this->_render($request);
    }

    public function getListSubject(Request $request){
        $sortTypes = ['createTimeDesc' => 1, 'createTimeAsc' => 2, 'idDesc' => 3, 'idAsc' => 4, 'articleNumDesc' => 5, 'articleNumAsc' => 6 ]; 
        $this->_validate($request, [
            'SortType'  => 'required|in:'.implode(",", array_values($sortTypes)),
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
            ]);
        $query = with(new \App\Subject)->with('cover_image');
        $total = $query->count();
        switch($request->input('SortType')){
        case $sortTypes['createTimeDesc']:
        case $sortTypes['idDesc']:
            $query = $query->orderBy('id', 'desc');
            break;
        case $sortTypes['createTimeAsc']:
        case $sortTypes['idAsc']:
            $query = $query->orderBy('id', 'asc');
            break;
        case $sortTypes['articleNumDesc']:
            $query = $query->orderBy('article_num', 'desc');
            break;
        case $sortTypes['articleNumAsc']:
            $query = $query->orderBy('article_num', 'asc');
            break;
        }
        $subjects = $query->take($request->input('PageSize'))->skip(($request->input('PageIndex')-1)*$request->input('PageSize'))->get();
        $this->output = ['SubjectList'=>[], 'Total' => $total];
        foreach($subjects as $c){
            $this->output['SubjectList'][] = [
                'SubjectId'    => $c->id,
                'LongName'     => $c->name,
                'ShortName'    => $c->name,
                'ImageUrl'     => url($c->cover_image->url),
                'Description'  => $c->brief,
                'TotalArticle' => $c->article_num,
                'UpdateTime'   => $c->updated_at->toDateTimeString(),
                'CreateTime'   => $c->created_at->toDateTimeString(),
                'CategoryList' => \App\Lib\Category::renderBreadcrumb($c->category_id),
                ];
        }
        return $this->_render($request);

    }

    public function getSubjectInfo(Request $request){
        $this->_validate($request, [
            'SubjectId'   => 'required|exists:subjects,id',
        ]);

        $subject = \App\Subject::find($request->input('SubjectId'));
        $this->output = [
            'SubjectId'    => $subject->id,
            'LongName'     => $subject->name,
            'ShortName'    => $subject->name,
            'ImageUrl'     => url($subject->cover_image->url),
            'Description'  => $subject->brief,
            'TotalArticle' => $subject->article_num,
            'UpdateTime'   => $subject->updated_at->toDateTimeString(),
            'CreateTime'   => $subject->created_at->toDateTimeString(),
            'CategoryList' => \App\Lib\Category::renderBreadcrumb($subject->category_id),
        ];
        return $this->_render($request);
    }

    public function getUserSetting(Request $request){
        $this->_validate($request, [
            'UserId'   => 'required|exists:users,id',
        ]);
        $user = \App\User::find($request->input('UserId'));
        $this->output = [ 'UserInfo' => [
            'UserName' => $user->name,
            'UserImage' => url($user->avatar->url),
            'Sex' => $user->sex,
            'Job' => $user->jod_id,
            'Area' => $user->area_id,
            ],
        ];
        $isSelf = $request->crUserId() == $request->input('UserId');
        if(!$isSelf){
            return $this->_render($request);
        }
        $this->output['AttentCate'] = [];
        $cates = \App\Category::whereIn('id', function($q) use($request){
            $q->select('category_id')->from(with(new \App\UserCategorySubscription)->getTable())
              ->where('user_id', $request->input('UserId'));
        })->get();
        foreach($cates as $c){
            $this->output['AttentCate'][] = \App\Lib\Category::renderBreadcrumb($c->id);
        }

        $this->output['PushState']    = $user->push_state; 
        $this->output['WhisperState'] = $user->whisper_state;
        $this->output['PhoneState']   = $user->phone_state;
        $this->output['PhotoState']   = $user->photo_state;
        return $this->_render($request);
    }

    public function setUserPassword(Request $request){
        $this->_validate($request, [
            'UserId'   => 'required|exists:users,id',
            'OldPassword'   => 'required',
            'NewPassword'   => 'required',
        ]);
        $this->output = [];
        $user = \App\User::find($request->input('UserId'));
        if($user->encrypt_pass != $request->input('OldPassword')){
            return $this->_render($request,false);
        }
        $user->encrypt_pass = $request->input('NewPassword');
        $user->save();
        return $this->_render($request);
    }

    public function getUserClub(Request $request){
        $this->_validate($request, [
            'UserId'   => 'required|exists:users,id',
        ]);
        $this->output = ['ClubList' => []];
        $arr = \App\Club::join('club_users', function($join){
                $join->on('clubs.id', '=', 'club_users.club_id')->where('club_users.has_exited', '=',  0);
            })
            ->with('cover_image')
            ->where('club_users.user_id', $request->input('UserId'))
            ->select('club_users.*', 'clubs.*' )->get();
        foreach($arr as $c){
            $this->output['ClubList'][] = [
                'ClubId'       => $c->id,
                'ClubName'     => $c->name,
                'ImageUrl'     => url($c->cover_image->url),
                'Description'  => $c->brief,
                'TotalUser'    => $c->user_num,
                'TotalArticle' => $c->article_num,
                'Letter'       => $c->letter,
                'UpdateTime'   => $c->updated_at->toDateTimeString(),
                'CreateTime'   => $c->created_at->toDateTimeString(),
                'CategoryList' => \App\Lib\Category::renderBreadcrumb($c->category_id),
            ];
        }
        return $this->_render($request);
    }

    public function getUserArticle(Request $request){
        $this->_validate($request, [
            'UserId'   => 'required|exists:users,id',
            'CateId'   => 'required|exists:categories,id',
            'PageIndex'   => 'required|integer',
            'PageSize'    => 'required|integer',
        ]);
        $user = \App\User::find($request->input('UserId'));
        $this->output['UserImage'] = url($user->avatar->url);
        $this->output['UserName']  = $user->name;
        $cate = \App\Category::find($request->input('CateId'));
        $this->output['CateName'] = $cate->name;
        $query = \App\Article::where('user_id', $request->input('UserId'))->where('category_id', $request->input('CateId'));
        $this->ouptpu['TotalArticle'] = $query->count();
        $this->ouptpu['TotalPraise'] = $query->sum('praise_num');
        $arr = $query->with('images')->skip( ($request->input('PageIndex') - 1)*$request->input('PageSize'))->take($request->input('PageSize'))->get();
        $this->output['ArticleList'] = [];
        foreach($arr as $article){
            $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 'Images' => [], 'Author' => [], 'CategoryList' => [] ];
            foreach($article->images as $image){
                $item['Images'][]=['ImageUrl' => url($image->url), 'Description' => $image->brief, 'Width' => $image->thumb_width, 'Height' => $image->thumb_height ]; 
            }
            $item['Author']['UserId']   = $article->user_id;
            $item['Author']['ImageUrl'] = url($article->user->avatar->url);
            $item['Author']['UserName'] = $article->user->name;
            $item['CategoryList'] = \App\Lib\Category::renderBreadcrumb($article->category_id);
            $this->output['ArticleList'][]=$item;
        }
        return $this->_render($request);
    }

    public function getUserCategory(Request $request){
        $this->_validate($request, [
            'UserId'   => 'required|exists:users,id',
        ]);
        $cates = \App\Category::join('user_category_subscriptions', function($join) use ($request){
            $join->on('categories.id', '=', 'user_category_subscriptions.category_id')
                ->where('user_category_subscriptions.user_id','=',  $request->input('UserId'));
        })->with('cover_image')->select("categories.*")->get();
        $this->output['CategoryList']=[];
        foreach($cates as $c){
            $this->output['CategoryList'][]=[
                'CateId' => $c->id,
                'ImageUrl' => url($c->cover_image->url),
                'CateName' => $c->name,
                'TotalArticle' => $c->article_num,
                'TotalPraise' => $c->total_praise,
            ];
        }
        return $this->_render($request);
    }

    public function setUserFollow(Request $request){
        $this->_validate($request, [
            'UserId'   => 'required|exists:users,id',
        ]);
        $relation           = \App\UserFollower::firstOrNew(['follower_id' => $request->crUserId(), 'user_id' => $request->input('UserId')]);
        $associate_relation = \App\UserFollower::firstOrNew(['user_id' => $request->crUserId(),     'follower_id' => $request->input('UserId')]);
        if($relation->id && $associate_relation->id){
            //nothing to do
        } else if($associate_relation->id){
            $relation->is_twoway = 1;
            $associate_relation->is_twoway = 1;
            $relation->save();
            $associate_relation->save();
        } else {
            $relation->is_twoway = 0;
            $relation->save();
        }
        return $this->_render($request);
    }

    public function getFindLike(Request $request){
        $this->output['ArticleList']  = [];
        $arr = \App\Article::join('category_articles', 'articles.id', '=', 'category_articles.article_id')
            ->join('user_category_subscriptions', 'category_articles.category_id', '=', 'user_category_subscriptions.category_id')
            ->with('user', 'user.avatar', 'images')
            ->select('articles.*')
            ->where('articles.user_id', "!=",  $request->crUserId())->orderBy('id', 'desc')->take(10)->get();
        if(count($arr)==0){
            $arr = \App\Article::join('category_articles', 'articles.id', '=', 'category_articles.article_id')
                ->with('user', 'user.avatar', 'images')
                ->select('articles.*')
                ->where('articles.user_id', "!=",  $request->crUserId())->orderBy('id', 'desc')->take(10)->get();
        }
        foreach($arr as $article){
            $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 'Images' => [], 'Author' => [], 'CategoryList' => [] ];
            foreach($article->images as $image){
                $item['Images'][]=['ImageUrl' => url($image->url), 'Description' => $image->brief, 'Width' => $image->thumb_width, 'Height' => $image->thumb_height ]; 
            }
            $item['Author']['UserId']   = $article->user_id;
            $item['Author']['ImageUrl'] = url($article->user->avatar->url);
            $item['Author']['UserName'] = $article->user->name;
            $item['CategoryList'] = \App\Lib\Category::renderBreadcrumb($article->category_id);
            $this->output['ArticleList'][]=$item;
        }

        $this->output['PhotoList'] = [];
        $arr = \App\Banner::where('page', config('shilehui.banner_page.guess_like'))->get();
        foreach($arr as $banner){
            $this->output['PhotoList'][] = [
                    'Title'     => $banner->title,
                    'ImageUrl'  => url($banner->url),
                    'H5Url'     => $banner->h5_link,
                    'AppUrl'    => $banner->app_link,
                ];
        }
        return $this->_render($request);
    }

    public function getFindHome(Request $request){
        $this->output['SubjectList']  = [];
        $this->output['CategoryList'] = [];
        $arr = \App\Subject::orderBy('id','desc')->take(6)->get();
        foreach($arr as $s){
            $this->output['SubjectList'][]  = [
                'SubjectId' => $s->id,
                'LongName'  => $s->name,
                'ShortName' => $s->alias,
                'ImageUrl'  => url($s->cover_image->url),
            ];
        }
        $arr = \App\Category::where('level', 1)->orderBy('id','desc')->take(6)->get();
        foreach($arr as $c){
            $this->output['CategoryList'][]  = [
                'SubjectId' => $c->id,
                'LongName'  => $c->name,
                'ShortName' => $c->alias,
                'ImageUrl'  => url($c->cover_image->url),
            ];
        }
        return $this->_render($request);
    }

    public function getHomeImage(Request $request){
        $this->output['ImageList'] = [];
        $arr = \App\Banner::where('page', config('shilehui.banner_page.home'))->get();
        foreach($arr as $banner){
            $this->output['ImageList'][] = [
                    'TragetModel' => "",
                    'ImageUrl'    => url($banner->url),
                    'H5Url'       => '',
                    'AppUrl'      => '',
                ];
        }
        return $this->_render($request);
    }

    public function getHomeFollow(Request $request){
        $this->_validate($request, [
            'UserId'    => 'required|exists:users,id',
            'PageIndex' => 'required|integer',
            'PageSize'  => 'required|integer',
        ]);
        $this->output['ArticleList'] = [];
        $q = \App\Article::leftJoin("user_followers", "articles.user_id", "=", "user_followers.user_id")
            ->where("user_followers.follower_id", $request->input('UserId'))
            ->orWhere("articles.user_id", $request->input('UserId'))
            ->select("articles.*")
            ->with("user", "images", "user.avatar");
        $this->output['Total'] = $q->count();
        $arr= $q->limit(($request->input("PageIndex")-1 ) * $request->input("PageSize"))
            ->take($request->input("PageSize"))->get();
        $item = [];
        foreach($arr as $article){
            $item = [
                'ArticleId' => $article->id,
                'Images'    => [],
                'CategoryList' => \App\Lib\Category::getBreadcrumb($article->category_id),
                'Author'    => [
                    'UserId'     => $article->user->id,
                    'ImageUrl'   => $article->user->avatar->url,
                    'UserName'   => $article->user->name,
                ],
                'TotalCollect' => $article->colloct_num,
                ];
            foreach($article->images as $image){
                $item['Images'][] = [
                    'Description' => $image->brief,
                    'Width'       => $image->width,
                    'Height'      => $image->height,
                    'ImageUrl'    => url($image->url),
                    ];
            }
            $this->output['ArticleList'][] = $item;

        }
        return $this->_render($request);
    }

    public function getHomeArticle(Request $request){
        $this->_validate($request, [
            'PageIndex' => 'required|integer',
            'PageSize'  => 'required|integer',
        ]);
        $this->output['ArticleList'] = [];
        $q = \App\Article::join("home_articles", "articles.id", "=", "home_articles.article_id")
            ->select("articles.*")
            ->with("user", "images", "user.avatar");
        $this->output['Total'] = $q->count();
        $arr= $q->limit(($request->input("PageIndex")-1 ) * $request->input("PageSize"))
            ->take($request->input("PageSize"))->get();
        $item = [];
        foreach($arr as $article){
            $item = [
                'ArticleId' => $article->id,
                'Images'    => [],
                'CategoryList' => \App\Lib\Category::getBreadcrumb($article->category_id),
                'Author'    => [
                    'UserId'     => $article->user->id,
                    'ImageUrl'   => $article->user->avatar->url,
                    'UserName'   => $article->user->name,
                ],
                'TotalCollect' => $article->colloct_num,
                ];
            foreach($article->images as $image){
                $item['Images'][] = [
                    'Description' => $image->brief,
                    'Width'       => $image->width,
                    'Height'      => $image->height,
                    'ImageUrl'    => url($image->url),
                    ];
            }
            $this->output['ArticleList'][] = $item;

        }
        return $this->_render($request);
    }


    public function getHotListClub(Request $request){
        $this->_validate($request, [
            'ShowNum' => 'required|integer',
        ]);
        $this->output = ['ClubList' => []];
        $arr = \App\Club::orderBy('today_article_num', 'desc')->take(20);
        foreach($arr as $club){
            $this->output['ClubList'][]= [
                'ClubId' => $club->id,
            ];
        }
        return $this->_render($request); 
    }
#    {   
#        ClubList: [
#            {
#                ClubId: <i>
#                ClubName: <i>
#                ImageUrl: <i>
#                Description: <i>
#                TotalUser: <i>
#                TotalArticle: <i>
#                Letter: <i>
#                UpdateTime: <i>
#                CreateTime: <i>
#                CategoryList: [
#                    {
#                        CateId: <i>
#                        CateName: <i>
#                    }
#                ]
#            }
#        ]





    public function unImplementMethod(){
        throw new \App\Exceptions\ApiException(['errorMessage' => 'not implement'], 403);
    }
}
