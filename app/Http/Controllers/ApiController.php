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
    protected function _render(Request $request, $ack = true){
        $this->output['Response'] = ['Time' => time(), 'State' => $request->crIsUserLogin(), 'Ack' => $ack ? 'Success' : 'Failure'];
        return response()->json($this->output);
    } 
    protected function _validate($request, $rules){
        $v = \Validator::make($request->all(), $rules);
        if($v->fails()){
            $this->output['Response'] = ['Time' => time(), 'State' => $request->crIsUserLogin(), 'Ack' => 'Failure'];
            throw new \App\Exceptions\ApiException(response()->json($this->output)); 
        }
    }
    public function unImplementMethod(){
        throw new \App\Exceptions\ApiException(response()->json(['error' => 'no this api'], 404));
    }
    public function getUserInfo(Request $request){
        $this->_validate($request, [
            'UserId' => 'required|exists:users,id',
            ]);
        $isViewMine = $request->input('UserId') == $request->crUserId();
        \App\Lib\User::refreshUserRecentUpdate($request->input('UserId'));//TODO use event instead of this line
        $user = \App\User::with('avatar')->where('id', $request->input('UserId'))->first();
        if(empty($user)){
            return $this->_render($request,false);
        }
        $needRefreshUserStat = false;
        $stat = \App\Lib\User::getUserStat($user->id, $needRefreshUserStat);
        $relation = \App\UserFollower::where('user_id', $user->id)->where('follower_id', $request->crUserId())->first();
        $this->output = [
            'UserImage' => empty($user->avatar) ? '' : url($user->avatar->url),
            'UserName'  => $user->name,
            'Sex'       => $user->sex,
            'Job'       => $user->job_id,
            'Area'      => $user->area_id,
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
            'PushState'    => $user->push_state, 
            'WhisperState' => $user->whisper_state,
            'PhoneState'   => $user->phone_state,
            'PhotoState'   => $user->photo_state,
            'StateFollow'  => empty($relation) ? 0 : 1,
            ];
        $this->output['AttentCate'] = [];
        $cates = \App\Category::whereIn('id', function($q) use($request){
            $q->select('category_id')->from(with(new \App\UserCategorySubscription)->getTable())
              ->where('user_id', $request->input('UserId'));
        })->get();
        foreach($cates as $c){
            $this->output['AttentCate'][] = \App\Lib\Category::renderBreadcrumb($c->id);
        }
        foreach($stat['latest_article_category'] as $c){
            $this->output['ArticleList'][] = [
                'CateId'   => $c->id,
                'ImageUrl' => empty($c->cover_image) ? "" : url($c->cover_image->url),
                'CateName' => $c->name,
                'TotalArticle' => $c->article_num,
                'TotalPraise'  => $c->praise_num,
            ];
        }
        foreach($stat['latest_collection_category'] as $c){
            $this->output['CollectList'][] = [
                'CateId'   => $c->id,
                'ImageUrl' =>  empty($c->cover_image) ? "" : url($c->cover_image->url),
                'CateName' => $c->name,
                'TotalArticle' => $c->article_num,
                'TotalPraise'  => $c->praise_num,
            ];
        }
        foreach($stat['latest_club'] as $c){
            $this->output['ClubList'][] = [
                'ClubId'    => $c->id,
                'ImageUrl'  => empty($c->cover_image) ? "" :  url($c->cover_image->url),
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

        $user = \App\User::with('avatar')->where('mobile', $request->input('Phone'))->first();
        if(empty($user)){
            return $this->_render($request,false);
        }
        $password = $request->input('Password');
        if( !env('APP_FAKEAUTH', false)){
            $encryptPass = \App\Lib\Auth::encryptPassword($password, $user->salt);
            if($user->encrypt_pass != $encryptPass) {
                \Log::info("password fail [$encryptPass][".$user->encrypt_pass."]"); 
                return $this->_render($request,false);
            }
        }
        $user->challenge_id = time();
        $user->save();
        $auth = new \App\Lib\Auth('API', $user->id);
        $sessUser = $auth->setUserAuth();
        if($auth->isRoleOf(config('shilehui.role.ban'))){
            return $this->_render($request, false);
        }
        $this->output = array_merge([
            'Auth'   => $sessUser['auth'],
            'Phone'  => $user->mobile,
            'Sex'      => $user->sex,
            'Area'     => $user->area_id,
            'Job'      => $user->job_id,
        ], \App\Lib\User::renderAuthor($user));
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
        $ErrorCodes = [
            'MobileExists' => 1,
            'PhoneCodeError' => 2,
        ];
        $this->_validate($request, [
            'UserName'    => 'required|string|min:2,max:32',
            'Sex'         => 'required|in:'.implode(",", config('shilehui.sex')),
            'Area'        => 'required',
            'Job'         => 'required',
            'Phone'       => 'required',
            'PhoneCode'   => 'required|string|min:2,max:6',
            'Password'    => 'required',
            ]);
        $user = \App\User::where('mobile', $request->input('Phone'))->first();
        if($user) {
            $this->output['ErrorCode'] = $ErrorCodes['MobileExists'];
            return $this->_render($request,false);
        }
        $type = config('shilehui.verify_code.fetch_password.id');
        $vc = \App\VerifyCode::where('phone', $request->input('Phone'))->where('type', $type)->first();
        if(empty($vc) || $vc->code != $request->input('PhoneCode') || $vc->is_expired){
            $this->output['ErrorCode'] = $ErrorCodes['PhoneCodeError'];
            return $this->_render($request,false);
        }

        $salt = rand(10000000, 99999999);
        $user = new \App\User;
        $user->mobile       = $request->input('Phone');
        $user->sex          = $request->input('Sex');
        $user->area_id      = $request->input('Area');
        $user->job_id       = $request->input('Job');
        $user->name         = $request->input('UserName');
        $user->salt         = $salt;
        $user->challenge_id = time();
        $user->encrypt_pass = \App\Lib\Auth::encryptPassword($request->input('Password'), $salt);
        $res = $user->save();
        $auth = new \App\Lib\Auth('API', $user->id);
        $sessUser = $auth->setUserAuth();
        $this->output = [
            'UserId' => $user->id,
            'Auth'   => $sessUser['auth'],
        ];
        return $this->_render($request);
    }
    public function setSendPhone(Request $request){
        $this->_validate($request, [
            'Phone'       => 'required',
            'Type'        => 'required',
        ]);
        $type = config('shilehui.verify_code.fetch_password.id');
        $phone = $request->input('Phone');
        $vc = \App\VerifyCode::firstOrNew(['phone' => $phone, 'type' => $type ]);
        $code = rand(111222, 999888);
        $vc->code = $code;
        $vc->save();
        \App\Lib\Sms::sendVerifyCode($type, $phone, $code );
        return $this->_render($request);
    }
    public function getCityList_1(Request $request) {
        $list = \App\Lib\Area::all();
        $this->output = $list;
        return $this->_render($request);
    }
    public function setArticlePost(Request $request) {
        $this->_validate($request, [
            'CateId' => 'exists:categories,id,is_leaf,1',
            'Images' => 'required|array',
            'ClubId' => 'exists:clubs,id',
            'ActivityId' => 'exists:activities,id',
            ]);
        $articleTypes = config('shilehui.article_type');
        $articleType = 0;
        $categoryId  = 0;
        $clubId      = 0;
        $activityId  = 0;
        if($request->input('ClubId')) {
            $isInClub = \App\ClubUser::where('club_id', $request->input('ClubId'))->where('user_id', $request->crUserId())
                ->where('has_exited',0)->first();
            if(empty($isInClub)) {
                return $this->_render($request,false);
            }
            if($request->input('CateId')){
                $articleType = $articleTypes['club'];
                $categoryId  = $request->input('CateId');
                $clubId      = $request->input('ClubId');
            }
        } else if($request->input('ActivityId')){
            $articleType = $articleTypes['activity'];
            $categoryId = \App\Activity::where('id', $request->input('ActivityId'))->pluck('to_category_id');
            $activityId = $request->input('ActivityId');
            if(!$categoryId) {
                return $this->_render($request,false);
            }
        } else if($request->input('CateId')) {
            $articleType = $articleTypes['normal'];
            $categoryId = $request->input('CateId');
        }
        if(!$articleType ) {
            return $this->_render($request,false);
        }
        $hasCommitTransaction = false;
        try{
        \DB::beginTransaction();
        $article = new \App\Article;
        $article->title = "";
        $article->category_id = $categoryId;
        $article->user_id = $request->crUserId();
        $article->club_id = $clubId;
        $article->activity_id = $activityId;
        $article->save();
        $imageCount = 0;
        foreach($request->input('Images') as $image){
            if(strlen($image['ImageUrl']) < 100 ) continue;
            $desc = empty($image['Description']) ? '' : $image['Description'];
            if(!$article->title) $article->title = $desc;
            $imageData    = \App\Lib\Image::decodeAndSaveAsTmp($image['ImageUrl'], $request->crUserId());
            $articleImage = new \App\ArticleImage;
            $articleImage->article_id  = $article->id;
            $articleImage->brief       = $desc;
            $articleImage->width       = $imageData['width'];
            $articleImage->height      = $imageData['height'];
            $articleImage->filename    = $imageData['name'];
            $articleImage->ext         = $imageData['ext'];
            $articleImage->size        = $imageData['size'];
            $articleImage->save();
            $imageCount++;
        }
        if($imageCount<=0){
            throw new Exception('');
        }
        $hasCommitTransaction = true;
        \DB::commit();
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

    public function getListArticle(Request $request){
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
            $ids = \App\Lib\Category::getDescendantsOf($request->input('CateId'))->lists('id');
            $query = \App\Article::join('category_articles', 'articles.id', '=', 'category_articles.article_id')
                ->whereIn('category_articles.category_id', $ids);
        } else if($request->input('SubjectId')) {
            $query = \App\Article::join('subject_articles','articles.id', '=', 'subject_articles.article_id')
                ->where('subject_articles.subject_id', $request->input('SubjectId'));
        } else if($request->input('ClubId')) {
            $query = \App\Article::join('club_articles','articles.id', '=', 'club_articles.article_id')
                ->where('club_articles.club_id', $request->input('ClubId'));
        } else if($request->input('ActivityId')) {
            $query = \App\Article::join('activity_articles','articles.id', '=', 'activity_articles.article_id')
                ->where('activity_articles.activity_id', $request->input('ActivityId'));
        } else if($request->input('UserId')) {
            $query = \App\Article::where('user_id', $request->input('UserId')); 
        } else {
            return $this->_render($request);
        }
        $total = $query->count();
        $articles = $query->with('images','user', 'user.avatar')->orderBy('articles.id','desc')
            ->select('articles.*')
            ->skip( ($request->input('PageIndex') - 1)*$request->input('PageSize'))
            ->take($request->input('PageSize'))->get();
        $this->output = ['ArticleList' => [], 'Total' => $total ];
        foreach($articles as $article){
            $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 'Images' => [], 'Author' => [], 'CategoryList' => [] ];
            foreach($article->images as $image){
                $item['Images'][] = \App\Lib\Image::renderImage($image,'thumb');
            }
            $item['Author']   = \App\Lib\User::renderAuthor($article->user);
            $item['CategoryList'] = \App\Lib\Category::renderBreadcrumb($article->category_id);
            $this->output['ArticleList'][]=$item;
        }
        return $this->_render($request);
    }



    public function getContentArticle(Request $request){
        $this->_validate($request, [
            'ArticleId'     => 'required_without:ActivityId|exists:articles,id',
            'ActivityId'    => 'required_without:ArticleId|exists:activities,id',
        ]);

        $logonUser = \App\User::find($request->crUserId());
        if($request->input('ActivityId')){
            $articleId = \App\ActivityArticle::where('activity_id', $request->input('ActivityId'))->pluck('article_id');
        } else {
            $articleId = $request->input('ArticleId');
        }

        $article = \App\Article::with('user','user.avatar')->where('id', $articleId)->first();
        if(empty($article)){
            return $this->_render($request, false);
        }

        $this->output = [
            'ArticleId'   => $article->id, 
            'UpdatedTime' => $article->user_updated_at->toDateTimeString(),
            'CreatedTime' => $article->created_at->toDateTimeString(),
            'Total' => [
                'TotalHit'     => $article->view_num, 
                'StatePraise'  => $article->is_praised_by_user($request->crUserId()) ? 2 : 1,
                'TotalPraise'  => $article->praise_num,
                'StateCollect' => $article->is_collected_by_user($request->crUserId()) ? 2 : 1,
                'TotalCollect' => $article->collection_num,
                'TotalShare'   => 0,
                'TotalComment' => $article->comment_num,
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
            'CommentList'  => [],
            'ArticleList'  => [],
        ];
        foreach($article->images as $image){
            $this->output['Images'][] = \App\Lib\Image::renderImage($image);
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


        $this->output['Author']   = \App\Lib\User::renderAuthor($article->user);
        $this->output['CategoryList']  = \App\Lib\Category::renderBreadcrumb($article->category_id);
        $arr = $article->praises()->with('user', 'user.avatar')->take(10)->get();
        foreach($arr as $pu){
            $this->output['PraiseUser'][] = \App\Lib\User::renderAuthor($pu->user);
        }
        $arr = $article->comments()->with('user','user.avatar')->orderBy('id', 'desc')->take(10)->get();
        foreach($arr as $c){
            $this->output['CommentList'][] = [
                'CommentId' => $c->id,
                'ArticleId' => $c->article_id,
                'Author'    => \App\Lib\User::renderAuthor($c->user),
                'UpdateTime'   => $c->updated_at->toDateTimeString(),
                'Content'      => $c->content,
            ];
        }
        $arr = \App\Article::join('users', 'articles.user_id', '=', 'users.id')->where('users.job', $article->user->job)
            ->select('articles.*')->orderBy('articles.view_num', 'desc')
            ->with('user','user.avatar')->take(6);
        foreach($arr as $a){
            $item = ['ArticleId' => $a->id, 'TotalCollect' => $a->collection_num, 
                'Images' => [], 'Author' => [], 'CategoryList' => [] ];
            foreach($a->images as $image){
                $item['Images'][] = \App\Lib\Image::renderImage($image, 'thumb');
            }
            $item['Author']  = \App\Lib\User::renderAuthor($a->user);
            $item['CategoryList'] = \App\Lib\Category::renderBreadcrumb($a->category_id);
            $this->output['ArticleList'][]=$item;
        }

        $article->view_num +=1;
        $article->save();

        return $this->_render($request);

    }


    public function getListCategory(Request $request){
        $this->_validate($request, [
            'CateId'     => 'exists:categories,id',
        ]);
        $cateId = $request->input('CateId', 0);
        $current = \App\Category::with('clubs')->where('id', $cateId)->first();
        $this->output['CurrentCate'] = \App\Lib\Category::renderDetail($current);
        $this->output['CategoryList'] = [];
        $arr = \App\Category::with('clubs')->where('parent_id', $cateId)->get();
        foreach($arr as $c){
            $item = \App\Lib\Category::renderDetail($c);
            $item['HasSub'] = !$c->is_leaf;
            $this->output['CategoryList'][] = $item;
        }
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
        $query = with(new \App\Club)->with('cover_image');
        $query = $query->orderBy('id', 'desc');
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
                'CategoryList' => \App\Lib\Category::renderBreadcrumb($c->to_category_id),
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
            'TotalAlwaysSign' => $attendance->continuous_days,
            'TotalSign'       => $attendance->total_days,
            'StateJoin'       => empty($clubUser) || $clubUser->has_exited ? false : true,
            'StateSign'       => $attendance->has_attended,
            'ActivityList'    => [
                'ActivityId'   => empty($club->activity) ? '' :  $club->activity->id,
                'ActivityName' => empty($club->activity) ? '' :  $club->activity->name,
                'ActivityType' => empty($club->activity) ? '' :  $club->activity->type,
            ],
            'ArticleTop'     => [ ],
            'CategoryList'    => \App\Lib\Category::renderBreadcrumb($club->category_id),
        ];
        $arr = \App\Article::join('club_articles', 'articles.id', '=', 'club_articles.article_id')
            ->where('club_articles.club_id', $club->id)->select('articles.*')
            ->with('user', 'images')
            ->orderBy('articles.collection_num', 'desc')->take(10);
            
        foreach($arr as $article){
            $item = [
                'ArticleId' => $article->id,
                'Images'    => [],
                'CategoryList' => \App\Lib\Category::getBreadcrumb($article->category_id),
                'Author'       => \App\Lib\renderAuthor($article->user),
                'TotalCollect' => $article->collection_num,
                ];
            foreach($article->images as $image){
                $item['Images'][] = \App\Lib\Image::renderImage($image,'thumb');
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
        $attendance = \App\Lib\UserClubAttendance::infoAt( $request->crUserId(), $request->input('ClubId'), $today->toDateString());
        if($attendance->has_attended){
            return $this->_render($request);
        }
        $todayAttendance = new \App\UserClubAttendance;
        $todayAttendance->user_id = $request->crUserId();
        $todayAttendance->club_id = $request->input('ClubId');
        $todayAttendance->attended_at = $today->toDateString();
        $todayAttendance->days        = $attendance->continuous_days + 1;
        if($todayAttendance->save()){
            //event(new \App\Events\UserClubAttend($clubUser->club_id, $clubUser->user_id));
        }
        return $this->_render($request);
    
    }

    public function getListComment(Request $request){
        $sortTypes = ['idDesc' => 1, 'idAsc' => 2];
        $this->_validate($request, [
            'SortType'   => 'required|integer|in:'.implode(",", array_values($sortTypes)),
            'ArticleId'  => 'required|exists:articles,id',
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ]);

        $query = \App\ArticleComment::where('article_id', $request->input('ArticleId'));
        $total = $query->count();
        switch ($request->input('SortType')) {
        case $sortTypes['idDesc']:
            $query = $query->orderBy('id', 'desc');
            break;
        case $sortTypes['idAsc']:
            $query = $query->orderBy('id', 'asc');
            break;
        }
        $comments = $query->with('user','user.avatar')->take($request->input('PageSize'))->skip( ($request->input('PageIndex')-1)*$request->input('PageSize'))->get();
        $this->output = ['CommentList' => [], 'Total' => $total];
        foreach($comments as $c){
            $this->output['CommentList'][] = [
                'CommentId' => $c->id,
                'ArticleId' => $c->article_id,
                'Author'    => \App\Lib\User::renderAuthor($c->user),
                'UpdateTime' => $c->updated_at->toDateTimeString(),
                'Content'    => $c->content,
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
        $comment->content = $request->input('Content');
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
        $loginUserId = $request->crUserId(); 
        $query = \App\UserFollower::leftJoin('user_followers as uf', function($join) use ($loginUserId){
            $join->on('uf.user_id','=','user_followers.follower_id')->where('uf.follower_id', '=', $loginUserId);
        })->where('user_followers.user_id', $request->input('UserId'))
            ->select('user_followers.*','uf.user_id as is_followed_by_me', 'uf.is_twoway as is_twoway_with_me');
        $total = $query->count();
        $relations = $query->with('follower', 'follower.avatar')->take($request->input('PageSize'))->skip(($request->input('PageIndex')-1)*$request->input('PageSize'))->get();
        $this->output = ['FansList' => [] ];
        foreach($relations as $r){
            $arr = \App\Lib\User::renderAuthor($r->follower);
            $arr['State'] = $r->is_followed_by_me ? 1 : 0;
            $this->output['FansList'][] = $arr;
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
        $loginUserId = $request->crUserId(); 
        $query = \App\UserFollower::leftJoin('user_followers as uf', function($join) use ($loginUserId){
            $join->on('uf.user_id','=','user_followers.user_id')->where('uf.follower_id', '=', $loginUserId);
        })->where('user_followers.follower_id', $request->input('UserId'))
            ->select('user_followers.*','uf.follower_id as is_followed_by_me', 'uf.is_twoway as is_twoway_with_me');
        $total = $query->count();
        $relations = $query->with('user','user.avatar')->take($request->input('PageSize'))->skip(($request->input('PageIndex')-1)*$request->input('PageSize'))->get();
        $this->output = ['FollowList' => [] ];
        foreach($relations as $r){
            $arr = \App\Lib\User::renderAuthor($r->user);
            $arr['State'] = $r->is_followed_by_me ? 1 : 0;
            $this->output['FollowList'][] = $arr;
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
                'ActivityLabel' => $a->alias,
                'ActivityType'  => $a->type,
                'ImageUrl'      => empty($a->cover_image) ? '' : url($a->cover_image->url),
                'Description'   => $a->brief,
                'UpdateTime'    => $a->updated_at->toDateTimeString(),
                'CreateTime'    => $a->created_at->toDateTimeString(),
                'CategoryList'  => \App\Lib\Category::renderBreadcrumb($a->to_category_id),
            ];
        }
        return $this->_render($request);
    }

    public function getContentActivity(Request $request){
        $this->_validate($request, [
            'ActivityId'   => 'required|exists:activities,id',
            ]);
        $a = \App\Activity::with('cover_image')->find($request->input('ActivityId'));
        $this->output['Content'] = [
            'ActivityId'   => $a->id,
            'ActivityName' => $a->name,
            'ImageUrl'     => empty($a->cover_image) ? '' : url($a->cover_image->url),
            'Description'  => $a->brief,
            'UpdateTime'   => $a->updated_at->toDateTimeString(),
            'CreateTime'   => $a->created_at->toDateTimeString(),
            'CategoryList' => \App\Lib\Category::renderBreadcrumb($a->to_category_id),
        ];
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
                'ImageUrl'     => empty($c->cover_image) ? '' : url($c->cover_image->url),
                'Description'  => $c->brief,
                'ClubId'       => empty($c->club) ? 0  : $c->club->id,
                'ClubName'     => empty($c->club) ? '' : $c->club->name,
                'TotalArticle' => $c->article_num,
                'UpdateTime'   => $c->updated_at->toDateTimeString(),
                'CreateTime'   => $c->created_at->toDateTimeString(),
                'CategoryList' => \App\Lib\Category::renderBreadcrumb($c->to_category_id),
                ];
        }
        return $this->_render($request);

    }

    public function getContentSubject(Request $request){
        $this->_validate($request, [
            'SubjectId'   => 'required|exists:subjects,id',
        ]);

        $subject = \App\Subject::with('club')->find($request->input('SubjectId'));
        $this->output = [
            'SubjectId'    => $subject->id,
            'LongName'     => $subject->name,
            'ShortName'    => $subject->name,
            'ImageUrl'     => empty($subject->cover_image) ? '' : url($subject->cover_image->url),
            'Description'  => $subject->brief,
            'ClubId'       => empty($c->club) ? 0 : $c->club->id,
            'ClubName'     => empty($c->club) ? 0 : $c->club->name,
            'TotalArticle' => $subject->article_num,
            'UpdateTime'   => $subject->updated_at->toDateTimeString(),
            'CreateTime'   => $subject->created_at->toDateTimeString(),
            'CategoryList' => \App\Lib\Category::renderBreadcrumb($subject->to_category_id),
        ];
        return $this->_render($request);
    }


    public function setUserPassword(Request $request){
        $this->_validate($request, [
            'UserId'   => 'required|exists:users,id',
            'OldPassword'   => 'required',
            'NewPassword'   => 'required',
            'PhoneCode'   => 'required',
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
                'ImageUrl'     => empty($c->cover_image) ? '' : url($c->cover_image->url),
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
        $user = \App\User::with('avatar')->where('id',$request->input('UserId'))->first();
        $this->output = \App\Lib\User::renderAuthor($user);
        $cate = \App\Category::find($request->input('CateId'));
        $this->output['CateName'] = $cate->name;
        $query = \App\Article::where('user_id', $request->input('UserId'))->where('category_id', $request->input('CateId'));
        $this->output['Total'] = $query->count();
        $this->output['TotalPraise'] = $query->sum('praise_num');
        $arr = $query->with('images','user','user.avatar')->orderBy('id','desc')
            ->skip( ($request->input('PageIndex') - 1)*$request->input('PageSize'))->take($request->input('PageSize'))->get();
        $this->output['ArticleList'] = [];
        foreach($arr as $article){
            $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 'Images' => [], 'Author' => [], 'CategoryList' => [] ];
            foreach($article->images as $image){
                $item['Images'][]=['ImageUrl' => url($image->thumb_url), 'Description' => $image->brief, 'Width' => $image->thumb_width, 'Height' => $image->thumb_height ]; 
            }
            $item['Author']   = \App\Lib\User::renderAuthor($article->user);
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
                'ImageUrl' => empty($c->cover_image) ? '' :  url($c->cover_image->url),
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
        \App\Lib\User::doFollow($request->crUserId(), $request->input('UserId'));
        return $this->_render($request);
    }

    public function getFindLike(Request $request){
        $user = \App\User::find($request->crUserId());
        $this->output['ArticleList']  = [];
        $arr1 = \App\Article::join('category_articles', 'articles.id', '=', 'category_articles.article_id')
            ->join('user_category_subscriptions', 'category_articles.category_id', '=', 'user_category_subscriptions.category_id')
            ->with('user', 'user.avatar', 'images')
            ->select('articles.*')
            ->where('articles.user_id', "!=",  $request->crUserId())
            ->orderBy('id', 'desc')->take(10)->get();
        $arr2 = \App\Article::join('category_articles', 'articles.id', '=', 'category_articles.article_id')
            ->join('users', 'users.id', '=', 'articles.user_id')
            ->with('user', 'user.avatar', 'images')
            ->select('articles.*')
            ->where('articles.user_id', "!=",  $request->crUserId())
            ->where('users.job_id', "=",  $user->job_id)
            ->orderBy('id', 'desc')->take(10)->get();

        $arr = $arr1->merge($arr2);
        foreach($arr as $article){
            $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 'Images' => [], 'Author' => [], 'CategoryList' => [] ];
            foreach($article->images as $image){
                $item['Images'][]=['ImageUrl' => url($image->url), 'Description' => $image->brief, 'Width' => $image->thumb_width, 'Height' => $image->thumb_height ]; 
            }
            $item['Author']  = \App\Lib\User::renderAuthor($article->user);
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
        $arr = \App\Category::with('cover_image')->where('level', 1)->orderBy('id','desc')->get();
        foreach($arr as $c){
            $this->output['CategoryList'][]  = \App\Lib\Category::render($c);
        }
        return $this->_render($request);
    }

    public function getHomeImage(Request $request){
        $this->output['ImageList'] = [];
        $arr = \App\Banner::where('page', config('shilehui.banner_page.home'))->get();
        foreach($arr as $banner){
            $this->output['ImageList'][] = [
                    'Title'       => $banner->name,
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
            'PageIndex' => 'required|integer',
            'PageSize'  => 'required|integer',
        ]);
        $loginUserId = $request->crUserId();
        $this->output['ArticleList'] = [];
        $q = \App\Article::whereExists(function($qr) use($loginUserId){
            $qr->select(\DB::raw(1))
                ->from('user_followers')
                ->whereRaw('articles.user_id = user_followers.user_id')
                ->where('follower_id',$loginUserId);
            })->orWhere("user_id", $loginUserId)
            ->with("user", "images", "user.avatar");
        $this->output['Total'] = $q->count();
        $arr= $q->skip(($request->input("PageIndex")-1 ) * $request->input("PageSize"))
            ->orderBy('id', 'desc')->take($request->input("PageSize"))->get();
        $item = [];
        foreach($arr as $article){
            $item = [
                'ArticleId' => $article->id,
                'Images'    => [],
                'CategoryList' => \App\Lib\Category::getBreadcrumb($article->category_id),
                'Author'    => \App\Lib\User::renderAuthor($article->user),
                'TotalCollect' => $article->collection_num,
                ];
            foreach($article->images as $image){
                $item['Images'][] = [
                    'Description' => $image->brief,
                    'Width'       => $image->thumb_width,
                    'Height'      => $image->thumb_height,
                    'ImageUrl'    => url($image->thumb_url),
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
        $arr= $q->skip(($request->input("PageIndex")-1 ) * $request->input("PageSize"))
            ->orderBy('id', 'desc')->take($request->input("PageSize"))->get();
        $item = [];
        foreach($arr as $article){
            $item = [
                'ArticleId' => $article->id,
                'Images'    => [],
                'CategoryList' => \App\Lib\Category::getBreadcrumb($article->category_id),
                'Author'    => \App\Lib\User::renderAuthor($article->user),
                'TotalCollect' => $article->collection_num,
                ];
            foreach($article->images as $image){
                $item['Images'][] = [
                    'Description' => $image->brief,
                    'Width'       => $image->thumb_width,
                    'Height'      => $image->thumb_height,
                    'ImageUrl'    => url($image->thumb_url),
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
        $this->output['ClubList'] = [];
        $arr = \App\Club::orderBy('today_article_num', 'desc')->take($request->input('ShowNum'))->get();
        foreach($arr as $club){
            $this->output['ClubList'][]= [
                'ClubId' => $club->id,
                'ClubName' => $club->name,
                'ImageUrl' => url($club->cover_image->url),
                'Description' => $club->brief,
                'TotalUser' => $club->user_num,
                'TotalArticle' => $club->article_num,
                'Letter' => $club->letter,
                'UpdateTime' => $club->updated_at->toDateTimeString(),
                'CreateTime' => $club->created_at->toDateTimeString(),
                'CategoryList' => \App\Lib\Category::getBreadcrumb($club->category_id),
            ];
        }
        return $this->_render($request); 
    }


    public function setArticlePraise(Request $request){
        $this->_validate($request, [
            'ArticleId'  => 'required|exists:articles,id',
        ]);
        $p = new \App\ArticlePraise;
        $p->article_id = $request->input('ArticleId');
        $p->user_id = $request->crUserId();
        $p->save();
        return $this->_render($request);
    }

    protected function _getListChat(Request $request, $isHistory){
        $this->_validate($request, [
            'UserId'     => 'required|exists:users,id',
            'ChatId'       => 'required|integer',
        ]);
        $this->output = ['ChatList' => [], 'UserInfo' => []];
        $littleUserId = min($request->input('UserId'), $request->crUserid());
        $greatUserId  = max($request->input('UserId'), $request->crUserid());
        $q = \App\Chat::where('little_user_id', $littleUserId)->where('great_user_id', $greatUserId);
        if($isHistory){
            $q = $q->where('id', '<', $request->input('ChatId'))->orderBy('id', 'desc');
        } else {
            $q = $q->where('id', '>', $request->input('ChatId'))->orderBy('id', 'asc');
        }
        $arr = $q->take(20)->get();
        foreach($arr as $a){
            $this->output['ChatList'][]=[
                'ChatId' => $a->id,
                'UpdateTime' => $a->updated_at->toDateTimeString(),
                'Message'    => $a->content,
                'Sender'     => $a->speak_user_id,
            ];
        }
        $arr = \App\User::with('avatar')->whereIn('id', [$littleUserId, $greatUserId])->get();
        foreach($arr as $a){
            $this->output['UserInfo'][] = \App\Lib\User::renderAuthor($a);
        }
        return $this->_render($request);
    }

    public function getListChatNews(Request $request){
        return $this->_getListChat($request, false);
    }
    public function getListChatHistory(Request $request){
        return $this->_getListChat($request, true);
    }
    
    public function getMsgNews(Request $request){
        $this->output = ['isPraise' => false, 'isComment' => false, 'isNotice' => false, 'isTalk' => false,];
        $arr = \App\Notification::where('user_id', $request->crUserId())->groupBy('type','has_read')->get();
        foreach($arr as $n){
            if($n->type == config('shilehui.notification_type.praise') && !$n->has_read)
                $this->output['isPraise'] = true;
            if($n->type == config('shilehui.notification_type.comment') && !$n->has_read)
                $this->output['isComment'] = true;
            if(in_array($n->type, array( config('shilehui.notification_type.notice'), config('shilehui.notification_type.follow'),  config('shilehui.notification_type.friend_register'),  config('shilehui.notification_type.welcome')  )) && !$n->has_read)
                $this->output['isNotice'] = true;
            if($n->type == config('shilehui.notification_type.chat') && !$n->has_read)
                $this->output['isTalk'] = true;
        }
        return $this->_render($request);
    }

    public function getMsgPraise_dynminac(Request $request){
        $this->output['PraiseList'] = [];
        $arr = \App\ArticlePraise::join("notifications", "article_praises.id", "=", "notifications.asso_id")
            ->where("notifications.type", config("shilehui.notification_type.praise"))
            ->where("notifications.user_id", $request->crUserId())
            ->select('article_praises.*', 'notifications.has_read')
            ->with('user', 'article', 'article.images')
            ->orderBy('article_praises.id', 'desc')
            ->take(100)->get();
        foreach($arr as $n){
            $this->output['PraiseList'][] = [
                'Author' => [
                    'UserId'   => $n->user->id,
                    'UserName' => $n->user->name,
                    'ImageUrl' => url($n->user->avatar_url),
                ],
                'Title'    => $n->article->title,
                'ImageUrl' => url($n->article->images[0]->url),
                'isRead'   => $n->has_read, 
            ];
        }
        return $this->_render($request);
    }

    public function getMsgPraise(Request $request){
        $this->output['PraiseList'] = [];
        $q = \App\Notification::where("type", config("shilehui.notification_type.praise"))
            ->where("user_id", $request->crUserId())
            ->with('sender' );
        $arr = $q->take(100)->get();
        $this->output['Total'] = $q->count();
        foreach($arr as $n){
            $this->output['PraiseList'][] = [
                'Author' => [
                    'UserId'   => $n->sender->id,
                    'UserName' => $n->sender->name,
                    'ImageUrl' => url($n->sender->avatar_url),
                ],
                'Title'    => $n->payload['article_title'],
                'ImageUrl' => url($n->payload['article_image_url']),
                'isRead'   => $n->has_read, 
            ];
        }
        return $this->_render($request);
    }
    public function getMsgComment_dyminac(Request $request){
        $this->output['CommentList'] = [];
        $arr = \App\ArticleComment::join("notifications", "article_comments.id", "=", "notifications.asso_id")
            ->where("notifications.type", config("shilehui.notification_type.comment"))
            ->where("notifications.user_id", $request->crUserId())
            ->select('article_comments.*', 'notifications.has_read')
            ->with('user', 'article', 'article.images')
            ->orderBy('article_comments.id', 'desc')
            ->take(100)->get();
        foreach($arr as $n){
            $this->output['CommentList'][] = [
                'CommentId' => $n->id,
                'Author' => [
                    'UserId'   => $n->user->id,
                    'UserName' => $n->user->name,
                    'ImageUrl' => url($n->user->avatar_url),
                ],
                'Article' => [
                    'ArticleId'   => $n->article->id,
                    'Description' => $n->article->images[0]->brief,
                    'ImageUrl'    => url($n->article->images[0]->url),
                ],
                'Content'    => $n->content,
                'UpdateTime' => $n->updated_at->toDateTimeString(),
                'isRead'     => $n->has_read, 
            ];
        }
        return $this->_render($request);
    }
    public function getMsgComment(Request $request){
        $this->output['CommentList'] = [];
        $q = \App\Notification::where("type", config("shilehui.notification_type.comment"))
            ->where("user_id", $request->crUserId())
            ->with('sender');
        $this->output['Total'] = $q->count();
        $arr = $q->take(100)->get();
        foreach($arr as $n){
            $this->output['CommentList'][] = [
                'CommentId' => $n->asso_id,
                'Author' => [
                    'UserId'   => $n->sender->id,
                    'UserName' => $n->sender->name,
                    'ImageUrl' => url($n->sender->avatar_url),
                ],
                'Article' => [
                    'ArticleId'   => $n->payload['article_id'],
                    'Description' => $n->payload['article_brief'],
                    'ImageUrl'    => url($n->payload['article_image_url']),
                ],
                'Content'    => $n->payload['content'],
                'UpdateTime' => $n->updated_at->toDateTimeString(),
                'isRead'     => $n->has_read, 
            ];
        }
        return $this->_render($request);
    }

    public function getMsgTalk(Request $request){
        $this->output['TalkList'] = [];
        /*
        $arr = \App\Chat::join("notifications", "chats.chat_id", "=", "notifications.asso_id")
            ->where("notifications.type", config("shilehui.notification_type.chat"))
            ->where("notifications.user_id", "=", $request->crUserId())
            ->select('chats.*', 'notifications.has_read', 'notifications.payload')
            ->take(100)->get();
         */
        $q = \App\Notification::where('type', config('shilehui.notification_type.chat'))
            ->where('user_id', $request->crUserId())
            ->with('sender')
            ->orderBy('updated_at', 'desc');
        $this->output['Total'] = $q->count();
        $arr = $q->take(100)->get();
        foreach($arr as $n){
            $this->output['TalkList'][] = [
                'Author' => [
                    'UserId'   => $n->sender->id,
                    'UserName' => $n->sender->name,
                    'ImageUrl' => url($n->sender->avatar_url),
                ],
                'Content'    => $n->payload['content'],
                'UpdateTime' => $n->updated_at->toDateTimeString(),
                'isRead'     => $n->has_read, 
            ];
        }
        return $this->_render($request);
    }
    public function getMsgNotice(Request $request){
        $types = [
            config('shilehui.notification_type.notice')          => 2,
            config('shilehui.notification_type.follow')          => 3, 
            config('shilehui.notification_type.friend_register') => 4,
            config('shilehui.notification_type.welcome')         => 1,
         ];

        $this->output['NoticeList'] = [];
        $q = \App\Notification::where('user_id', $request->crUserId())
            ->where('type',  array( config('shilehui.notification_type.notice'),
                config('shilehui.notification_type.follow'), 
                config('shilehui.notification_type.friend_register'),
                config('shilehui.notification_type.welcome') ))
            ->with('sender');
        $this->output['Total'] = $q->count();
        $arr = $q->take(100)->get();
        foreach($arr as $n){
            $this->output['NoticeList'][] = [
                'Author' => [
                    'UserId'   => $n->sender->id,
                    'ImageUrl' => ($n->sender->avatar->url),
                    'UserName' => $n->sender->name,
                ],
                'UpdateTime' => $n->updated_at->toDateTimeString(),
                'Content' => $n->payload['content'],
                'Type'    => $types[$n->type],
                'isRead'  => $n->has_read,
            ];

        }
        return $this->_render($request);

    }
    public function setModifyPassword(Request $request){
        $this->_validate($request, [
            'OldPassword'  => 'required|string|min:6',
            'Password'     => 'required|string|min:6',
            'PhoneCode'    => 'required|string|min:6',
        ]);

        $user = \App\User::find($request->crUserId());
        $vc = \App\VerifyCode::where('phone', $user->mobile)->where('type', config('shilehui.verify_code.fetch_password.id'))->first();
        if(empty($user) ||  empty($vc) || $request->input('PhoneCode') != $vc->code || $vc->is_expired){
            return $this->_render($request, false);
        }
        
        $oldPassword = $request->input('OldPassword');
        $encryptPass = \App\Lib\Auth::encryptPassword($oldPassword, $user->salt);
        if($user->encrypt_pass != $encryptPass) {
            \Log::info("old password fail [$encryptPass][".$user->encrypt_pass."]"); 
            return $this->_render($request,false);
        }

        $password = $request->input('Password');
        $user->salt = rand(11122233,99988877);
        $user->encrypt_pass = \App\Lib\Auth::encryptPassword($password, $user->salt);
        $user->challenge_id = time();
        $user->save();
        $vc->code = rand(123456789,987654321);
        $vc->save();
        $auth = new \App\Lib\Auth('API', $user->id);
        $sessUser = $auth->setUserAuth();
        $this->output = ['UserId' => $user->id, 'Auth' => $sessUser['auth']];
        return $this->_render($request);
    }
    public function setNewPassword(Request $request){
        $this->_validate($request, [
            'Phone'       => 'required|exists:users,mobile',
            'PhoneCode'   => 'required|string|min:6',
            'Password'    => 'required|string|min:6',
        ]);
        $type = config('shilehui.verify_code.fetch_password.id');
        $phone = $request->input('Phone');
        $code  = $request->input('PhoneCode', '');
        $vc = \App\VerifyCode::where('phone', $phone)->where('type',$type)->first();
        $u = \App\User::where('mobile', $phone)->first();
        if(empty($u) || !$code || empty($vc) || $code != $vc->code || $vc->is_expired){
            return $this->_render($request, false);
        }
        $u->salt = rand(11122233,99988877);
        $u->encrypt_pass = \App\Lib\Auth::encryptPassword($request->input('Password'), $u->salt);
        $u->challenge_id = time();
        $u->save();
        $vc->code = rand(123456789,987654321);
        $vc->save();
        return $this->_render($request);
    }

    public function setUserInfo(Request $request){
        $this->_validate($request, [
            'UserInfo.Job'       => 'required|integer',
            'UserInfo.Sex'       => 'required|integer',
            'UserInfo.Area'      => 'required|integer',
        ]);
        $user = \App\User::find($request->crUserId());
        $user->job_id  = $request->input('UserInfo.Job');
        $user->area_id = $request->input('UserInfo.Area');
        $user->sex     = $request->input('UserInfo.Sex');
        $user->push_state    = $request->input('PushState',  false);
        $user->Whisper_state = $request->input('WhisperState',  false);
        $user->Phone_state   = $request->input('PhoneState',  false);
        $user->Photo_state   = $request->input('PhotoState',  false);
        $user->save();
        return $this->_render($request);
    }
    public function getUserArticleCate(Request $request){
        $this->_validate($request, [
            'UserId'     => 'required|exists:users,id',
        ]);
        $this->output['CategoryList'] = [];
        $arr = \App\Article::with('category')->where('user_id', $request->input('UserId'))->groupBy('category_id')
            ->select(\DB::raw('sum(praise_num) as total_praise'), \DB::raw('count(*) as total_article'), 'category_id')
            ->get();
        foreach($arr as $a){
            $this->output['CategoryList'][] = [
                'CateList' => \App\Lib\Category::renderBreadcrumb($a->category_id),
                'TotalArticle' => $a->total_article,
                'TotalPraise'  => $a->total_praise,
            ];
        }
        return $this->_render($request);
    }
    public function getUserCollectCate(Request $request){
        $this->_validate($request, [
            'UserId'     => 'required|exists:users,id',
        ]);
        $this->output['CategoryList'] = [];
        $arr = \App\Article::join('article_collections', 'articles.id', '=', 'article_collections.article_id')
            ->with('category')->where('article_collections.user_id', $request->input('UserId'))->groupBy('category_id')
            ->select(\DB::raw('sum(praise_num) as total_praise'), \DB::raw('count(*) as total_article'), 'category_id')
            ->get();
        foreach($arr as $a){
            $this->output['CategoryList'][] = [
                'CateList' => \App\Lib\Category::renderBreadcrumb($a->category_id),
                'TotalArticle' => $a->total_article,
                'TotalPraise'  => $a->total_praise,
            ];
        }
        return $this->_render($request);
    }
    public function getUserCollect(Request $request){
        $this->_validate($request, [
            'UserId'   => 'required|exists:users,id',
            'CateId'   => 'required|exists:categories,id',
            'PageIndex'   => 'required|integer',
            'PageSize'    => 'required|integer',
        ]);
        $user = \App\User::with('avatar')->where('id',$request->input('UserId'))->first();
        $this->output = \App\Lib\User::renderAuthor($user);
        $cate = \App\Category::find($request->input('CateId'));
        $this->output['CateName'] = $cate->name;
        $query = \App\Article::join('article_collections', 'articles.id', '=', 'article_collections.article_id')
            ->where('article_collections.user_id', $request->input('UserId'))->where('category_id', $request->input('CateId'));
        $this->output['Total'] = $query->count();
        $this->output['TotalPraise'] = $query->sum('praise_num');
        $arr = $query->select("articles.*")->with('images','user','user.avatar')->orderBy('id','desc')
            ->skip( ($request->input('PageIndex') - 1)*$request->input('PageSize'))->take($request->input('PageSize'))->get();
        $this->output['ArticleList'] = [];
        foreach($arr as $article){
            $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 'Images' => [], 'Author' => [], 'CategoryList' => [] ];
            foreach($article->images as $image){
                $item['Images'][]=['ImageUrl' => url($image->thumb_url), 'Description' => $image->brief, 'Width' => $image->thumb_width, 'Height' => $image->thumb_height ]; 
            }
            $item['Author']   = \App\Lib\User::renderAuthor($article->user);
            $item['CategoryList'] = \App\Lib\Category::renderBreadcrumb($article->category_id);
            $this->output['ArticleList'][]=$item;
        }
        return $this->_render($request);
    }
    public function setListChat(Request $request){
        $this->_validate($request, [
            'UserId'     => 'required|exists,users,id',
            'Message'    => 'required|string|min:1',
        ]);
        $listener_id = $request->input('UserId');
        $speaker_id = $request->crUserId();
        $chat = \App\Chat::firstOrCreate(['little_user_id' =>  min($listener_id, $speaker_id), 'great_user_id' => max($listener_id, $speaker_id)]);
        $message = new \App\ChatMessage;
        $message->chat_id = $chat->id;
        $message->user_id = $speaker_id;
        $message->content = $request->input('Message');
        $message->save();
        return $this->_render($request);
    }
    public function setUserImage(Request $request){
        $this->_validate($request, [
            'ImageData'     => 'required',
        ]);
        $imageData    = \App\Lib\Image::decodeAndSaveAsTmp($request->input('ImageData'), $request->crUserId());
        $user = \App\User::find($request->crUserId());
        $avatar = new \App\UserAvatar;
        $avatar->user_id = $request->crUserId();
        $avatar->filename    = $imageData['name'];
        $avatar->ext         = $imageData['ext'];
        $avatar->save();
        $user->user_avatar_id = $avatar->id;
        $user->save();
        \App\Lib\Image::moveToDestination($imageData['name'], $imageData['ext']);
        $this->output['ImageUrl'] = url($avatar->url);
        return $this->_render($request);


    }
    public function setArticleCollect(Request $request){
        $this->_validate($request, [
            'ArticleId'  => 'required|exists:articles,id',
        ]);
        $p = new \App\ArticleCollection;
        $p->article_id = $request->input('ArticleId');
        $p->user_id = $request->crUserId();
        $p->save();
        return $this->_render($request);
    }
    public function getSearch(Request $request){
        $this->output['KeywordList'] = [];
        $arr = \App\Keyword::all();
        foreach($arr as $kw){
            $this->output['KeywordList'][] = ['Keyword' => $kw->name]; 
        }
        return $this->_render($request);
    }
    public function getSearchContent(Request $request){
        $types = ['all' => 0, 'article' => 1, 'category' => 2, 'club' => 3, 'user' => 4 ];
        $this->_validate($request, [
            'Keyword'  => 'required|string',
            'Type'     => 'required|in:'.implode(",", $types),
            ]);
        $keyword = $request->input('Keyword');
        $type = $request->input('Type');

        if( $type == $types['all'] || $type == $types['article']){
            $this->output['ArticleList'] = [];
            $arr = \App\Article::whereIn('id', function($q) use($keyword){
                $q->select('article_id')->from('article_images')
                    ->where('brief', 'like', "%$keyword%");
            })->orderBy('id','desc')->take(20)->get();
            foreach($arr as $article){
                $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 
                    'Images' => [], 'Author' => [], 'CategoryList' => [] ];
                foreach($article->images as $image){
                    $item['Images'][] = \App\Lib\Image::renderImage($image,'thumb');
                }
                $item['Author']   = \App\Lib\User::renderAuthor($article->user);
                $item['CategoryList'] = \App\Lib\Category::renderBreadcrumb($article->category_id);
                $this->output['ArticleList'][]=$item;
            }
        }

        if($type == $types['all'] || $type == $types['category']){
            $this->output['CategoryList'] = [];
            $arr = \App\Category::with('cover_image')->where('name', 'like', "%$keyword%")->take(20)->get();
            foreach($arr as $c){
                $item = \App\Lib\Category::render($c);
                $item['HasSub'] = !$c->is_leaf;
                $this->output['CategoryList'][] = $item;
            }
        }
        if($type == $types['all'] || $type == $types['club']){
            $this->output['ClubList'] = [];
            $arr=\App\Club::with('cover_image')->where('name', 'like', "%$keyword%")->take(20)->get();
            foreach($arr as $c){
                $this->output['ClubList'][] = [
                    'ClubId'       => $c->id,
                    'ClubName'     => $c->name,
                    'ImageUrl'     => empty($c->cover_image) ? '' : url($c->cover_image->url),
                    'Description'  => $c->brief,
                    'TotalUser'    => $c->user_num,
                    'TotalArticle' => $c->article_num,
                    'Letter'       => $c->letter,
                    'UpdateTime'   => $c->updated_at->toDateTimeString(),
                    'CreateTime'   => $c->created_at->toDateTimeString(),
                    'Category' => \App\Lib\Category::renderBreadcrumb($c->to_category_id),
                    ];
            }
        }
        if($type == $types['all'] || $type == $types['user']){
            $this->output['UserList'] = [];
            $arr = \App\User::with('avatar')->where('name', 'like', "%$keyword%")->take(20)->get();
            foreach($arr as $c){
                $this->output['UserList'][] = \App\Lib\User::renderAuthor($c);
            }
        }

        return $this->_render($request);

    }
    public function setAttendCate(Request $request){
        $this->_validate($request, [
            'CatesId'  => 'required|array',
        ]);
        foreach($request->input('CatesId') as $cid){
            \App\UserCategorySubscription::firstOrCreate(['category_id' => $cid, 'user_id' => $request->crUserId()]);
        }
        return $this->_render($request);
    } 
    public function getRegFollow(Request $request){
        $this->output = ['ListJob' => [], 'ListCity' => [] ];
        $loginUser = \App\User::find($request->crUserId());
        $users = [];
        $users['ListJob'] = \app\user::where('job_id', $loginUser->job_id)->with(['limited_article' => function($q){
            $q->orderby('id', 'desc');
            }])->with('limited_article.images','avatar')
            ->orderby('article_num', 'desc')->take(5)->get();
        $users['ListCity'] = \app\user::where('area_id', $loginUser->area_id)->with(['limited_article' => function($q){
            $q->orderby('id', 'desc');
            }])->with('limited_article.images','avatar')
            ->orderby('article_num', 'desc')->take(5)->get();
        foreach($users as $k => $us){
            foreach($us as $u){
                $arr = [
                    'Author' => \App\Lib\User::renderAuthor($u),
                    'ArticleList' => [],
                ];
                $i=5;
                foreach($u->limited_article as $article){
                    if($i--<=0) break;
                    $tarr = [ 
                        'ArticleId' => $article->id,
                        'Images' => [],
                    ];
                    foreach($article->images as $img){
                        $tarr['Images'][] = [
                            'ImageUrl'    => url($img->thumb_url),
                            'Description' => $img->brief,
                            'Width'       => $img->thumb_width,
                            'Height'      => $img->thumb_height,
                        ];
                    }
                    $arr['ArticleList'][] = $tarr;
                }
                $this->output[$k][] = $arr;
            }
        }

        return $this->_render($request);

    }
    public function setRegFollow(Request $request){
        $this->_validate($request, [
            'UsersId'  => 'required|array',
        ]);
        $users = \App\User::whereIn('id', $request->input('UsersId'))->get();
        foreach($users as $u){
            \App\Lib\User::doFollow($request->crUserId(), $u->id);
        }
        return $this->_render($request);
    }
    //TODO 所有栏目都要加上hasSub属性，表示是否有子栏目
}

