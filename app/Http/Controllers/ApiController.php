<?php namespace App\Http\Controllers;

use App\Http\Requests;
//use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller {
    protected $data;
	public function __construct() {
        parent::__construct();
        $this->data = [ 'Response' => [
            'Time'  => time(),
            'State' => 200,
            'Ack'   => 'Success',
            ]];
	}
    private function _render($data, $responseData=[], $type=null){
        $this->data['Response'] = array_merge($this->data['Response'], $responseData);
        $output = array_merge($this->data, $data);
        return response()->json($output);
    } 
    private function _validate($request, $rules, $resData){
        $v = \Validator::make($request->all(), $rules);
        if($v->fails()){
            throw new \App\Exceptions\ApiValidateException(response()->json([
                'time'=>time(), 'state' => $resData['State'], 'Ack'=>'Success', 'ErrMsg'=>$v->messages()], 500));
        }
    }
    public function getUserInfo(Request $request){
        $this->_validate($request, [
            'UserId' => 'required|exists:users,id',
            ],['State' => 201]);
        $isViewMine = $request->input('UserId') == $this->auth['user']['id'];
        $user = \App\User::find($request->input('UserId'));
        if(empty($user)){
            return $this->_render([], ['State' => 201]);
        }
        $stat = \App\Lib\User::getUserStat($user->id);
        $articleList = [];
        return $this->_render([
            'UserImage' => url($user->user_image_url),
            'Username'  => $user->name,
            'Exper'     => $user->exp_num,
            'RankName'  => '',
            'TotalFollow' => $user->follow_num,
            'TotalFans'   => $user->fans_num,
            'ArticleList' => $stat['latest_article_category'],
            'CollectList' => $stat['latest_collection_category'],
            'ClubList'    => $stat['latest_club'],
            'TotalCollect' => $user->collection_num,
            'TotalArticle' => $user->article_num,
            'TotalClub'    => $user->club_num,
            ]);
    }
    public function index(Request $request){
        return $this->_render([]);
    }
    public function getLogin(Request $request){
        $this->_validate($request, [
            'Phone'    => 'required|numeric',
            'Password' => 'required',
            ], ['State'=>201]);

        $user = \App\User::where('mobile', $request->get('Phone'))->first();
        if(empty($user)){
            return $this->_render([], ['State' => 202]);
        }
        $password = $request->get('Password');
        //$password = \App\Lib\Auth::descrpt_password($request->get('Password'));
        //if($user->encrypt_password != \App\Lib\Auth::encryptPassword($password, $user->salt)){
        if($user->encrypt_pass != $password){
            return $this->_render([], ['State' => 203]);
        }
        $user->challenge_id = time();
        $user->save();
        \App\Lib\Auth::setUserAuth($user->id);
        return $this->_render(['UserId' => $user->id, 'Auth' => $authString]);
    }
    public function getLogout(Request $request) {
    }
    public function setRegInfo(Request $request) {
        $this->_validate($request, [
            'UserName'    => 'required|string|min:2,max:32',
            'Sex'         => 'required|in:'.implode(",", config('shilehui.sex')),
            'Area'        => 'required|exists:areas,id',
            'Job'         => 'required|exists:jobs,id',
            'Phone'       => 'required|',
            'Password'    => 'required',
            ], ['State'=>201]);
        $user = \App\User::where('mobile', $request->input('Phone'))->first();
        if($user) {
            return $this->_render([], ['State' => 202]);
        }
        //$user = \App\User::firstOrNew(['mobile', $request->input('Phone')]);
        $user = new \App\User;
        $user->mobile = $request->input('Phone');
        $user->sex = $request->input('Sex');
        $user->area_id = $request->input('Area');
        $user->job_id = $request->input('Job');
        $user->name = $request->input('UserName');
        $user->encrypt_pass = $request->input('Password');
        $res = $user->save();
        return $this->_render(['UserId'=>$user->id ]);
    }
    public function getCityList_1(Request $request) {
        $list = \App\Lib\Area::all();
        return $this->_render($list);
    }
    public function setArticle(Request $request) {
        $this->_validate($request, [
            'Title' => 'required|string|min:5,max:256',
            'Category' => 'required|exists:categories,id',
            'Images' => 'required|array',
            'Club' => 'exists:clubs,id',
            'Activity' => 'exists:activities,id',
            ], ['State' => 201]);
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
        $article->user_id = $this->auth['user']['id'];
        $article->save();
        foreach($request->input('Images') as $image){
            if(strlen($image['File']) < 100 ) continue;
            $imageData    = \App\Lib\Image::decodeAndSaveAsTmp($image['File'], $this->auth['user']['id']);
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
            return $this->_render([],['State' => 202]);
        }
        $images = $article->images;
        foreach($images as $image){
            \App\Lib\Image::moveToDestination($image->filename, $image->ext);
        }
        event(new \App\Events\UserArticlePost($article->id, $articleType, [])); 
        return $this->_render([]);
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
            ], ['State' => 201]);
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
            return $this->_render([]);
        }
        $total = $query->count();
        $articles = $query->with('images','user')->skip( ($request->input('PageIndex') - 1)*$request->input('PageSize'))->take($request->input('PageSize'))->get();
        $output = ['ArticleList' => [], 'Total' => $total ];
        foreach($articles as $article){
            $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 'Images' => [], 'Author' => [], 'CategoryList' => [] ];
            foreach($article->images as $image){
                $item['Images'][]=['ImageUrl' => url($image->url), 'Description' => $image->brief, 'Width' => $image->thumb_width, 'Height' => $image->thumb_height ]; 
            }
            $item['Author']['UserId']   = $article->user_id;
            $item['Author']['ImageUrl'] = url($article->user->user_image_url);
            $item['Author']['UserName'] = $article->user->name;
            $item['CategoryList'] = \App\Lib\Category::renderBreadcrumb($article->category_id);
            $output['ArticleList'][]=$item;
        }
        //var_dump(\DB::getQueryLog());
        return $this->_render($output);



    }



    public function getContentArticle(Request $request){
        $this->_validate($request, [
            'ArticleId'     => 'exists:articles,id',
            ], ['State' => 201]);
        
        $article = \App\Article::find($request->input('ArticleId'));


        $output = [
            'ArticleId'   => $article->id, 
            'UpdatedTime' => $article->user_updated_at->toDateTimeString(),
            'CreatedTime' => $article->created_at->toDateTimeString(),
            'Total' => [
                'TotalHit'     => $article->view_num, 
                'StatePraise'  => $article->is_praised_by_user($this->auth['user']['id']) ? 2 : 1,
                'TotalPraise'  => $article->praise_num,
                'StateCollect' => $article->is_collected_by_user($this->auth['user']['id']) ? 2 : 1,
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
            $output['Images'][]=['ImageUrl' => url($image->url), 'Description' => $image->brief, 'Width' => $image->thumb_width, 'Height' => $image->thumb_height ]; 
        }
        if($article->club){
            $output['Affect']['ClubId']   = $article->club->id;
            $output['Affect']['ClubName'] = $article->club->name;
        }
        if($article->subject){
            $output['Affect']['SubjectId']   = $article->subject->id;
            $output['Affect']['SubjectName'] = $article->subject->name;
        }
        if($article->activity){
            $output['Affect']['ActivityId']   = $article->activity->id;
            $output['Affect']['ActivityName'] = $article->activity->name;
        }


        $output['Author']['UserId']   = $article->user->id;
        $output['Author']['ImageUrl'] = url($article->user->user_image_url);
        $output['Author']['UserName'] = $article->user->name;
        $output['CategoryList']  = \App\Lib\Category::renderBreadcrumb($article->category_id);
        $praiseUsers = \App\ArticlePraise::with('user')->where('article_id', $article->id)->take(10)->get();
        foreach($praiseUsers as $pu){
            $output['PraiseUser'][] = ['UserId' => $pu->user_id, 'UserName' => $pu->user->name, 'ImageUrl' => url($pu->user->user_image_url)];
        }

        return $this->_render($output);

    }

    public function getCityList(Request $request){
        $areas = \App\Lib\Area::all();
        $output=['List' => []];
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
            $output['List'][] = $arrP;
        }
        return $this->_render($output);

    }

    public function getListCategory(Request $request){
        $scopeTypes = ['child' => 1, 'descendant' => 2, 'brother' =>3]; 
        $this->_validate($request, [
            'CateId'     => 'exists:categories,id',
            'CateType'   => 'required|in:'.implode(",", array_values($scopeTypes)),
            ], ['State' => 201]);
        $cateId = $request->input('CateId', 0);
        $scopeType = $request->input('CateType');

        //Todo
        return $this->_render([]);
    
    }

    public function setReportArticle(Request $request){
        $this->_validate($request, [
            'ArticleId'  => 'exists:articles,id',
            'Contact'    => 'required',
            'ReportReason' => 'required',
            ], ['State' => 201]);
        $ar = new \App\ArticleReport();
        $ar->article_id = $request->input('ArticleId');
        $ar->contact    = $request->input('Contact');
        $ar->reason     = $request->input('ReportReason');
        $ar->user_id    = $this->auth['user']['id'];
        $ar->save();
        return $this->_render([]);

    }


    public function getListClub(Request $request){
        $sortTypes = ['todayPosts' => 1, 'id' => 2, 'postTimeDesc' => 3, 'postTimeAsc' => 4, 'letter' => 5]; 
        $this->_validate($request, [
            'SortType'  => 'required|in:'.implode(",", array_values($sortTypes)),
            'UserId'     => 'exists:users,id',
            ], ['State' => 201]);
        $query = with(new \App\Club);
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
            return $this->_render([],['State'=>201]);
        }
        $clubs = $query->get();
        $output=['ClubList'=>[]];
        foreach($clubs as $c){
            
            $output['ClubList'][] = [
                'ClubId'       => $c->id,
                'ClubName'     => $c->name,
                'ImageUrl'     => url($c->cover_image_url),
                'Description'  => $c->brief,
                'TotalUser'    => $c->user_num,
                'TotalArticle' => $c->article_num,
                'Letter'       => $c->letter,
                'UpdateTime'   => $c->updated_at->toDateTimeString(),
                'CreateTime'   => $c->created_at->toDateTimeString(),
                'CategoryList' => \App\Lib\Category::renderBreadcrumb($c->category_id),
                ];
        }
        return $this->_render($output);

    }

    public function getContentClub(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'exists:clubs,id',
            ], ['State' => 201]);
        
        $club       = \App\Club::find($request->input('ClubId'));
        $clubUser   = \App\ClubUser::where('user_id', $this->auth['user']['id'])->where('club_id', $club->id)->first();
        $attendance = \App\Lib\UserClubAttendance::infoAt($this->auth['user']['id'], $club->id, \Carbon\Carbon::now());
        $output=[
            'ClubId'      => $club->id,
            'ClubName'    => $club->name,
            'Description' => $club->brief,
            'ImageUrl'    => url($club->cover_image_url),
            'TotalMember' => $club->user_num,
            'TotalSign'   => $attendance->continuous_days,
            'TotalAlwaysSign' => $attendance->total_days,
            'StateJoin'       => empty($clubUser) ? false : true,
            'StateSign'       => $attendance->has_attended,
            'CategoryList'    => \App\Lib\Category::renderBreadcrumb($club->category_id),
        ];

        return $this->_render($output);
    
    }

    public function setJoinClub(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'required|exists:clubs,id',
        ], ['State' => 201]);
        $clubUser = \App\ClubUser::firstOrNew(['user_id' =>  $this->auth['user']['id'], 'club_id' => $request->input('ClubId')]);
        if($clubUser->id && !$clubUser->has_exited){
            return $this->_render([]);
        }
        $clubUser->has_exited = 0;
        if($clubUser->save()){
            event(new \App\Events\UserClubJoin($clubUser->club_id, $clubUser->user_id));
        }
        return $this->_render([]);
    
    }

    public function setLeaveClub(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'required|exists:clubs,id',
        ], ['State' => 201]);
        $clubUser = \App\ClubUser::where('user_id',  $this->auth['user']['id'])->where('club_id', $request->input('ClubId'))->first();
        if(!$clubUser || $clubUser->has_exited){
            return $this->_render([]);
        }
        $clubUser->has_exited = 1;
        if($clubUser->save()){
            event(new \App\Events\UserClubExit($clubUser->club_id, $clubUser->user_id));
        }
        return $this->_render([]);
    
    }

    public function getClubHotUser(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'required|exists:clubs,id',
        ], ['State' => 201]);
        $hotUsers = \App\ClubTopUser::with('\App\User')->where('club_id', $request->input('ClubId'))->orderBy('article_num', 'desc')->get();
        $output = ['UserList' => []];
        foreach($hotUsers as $hu){
            $output['UserList'][]=[
                'UserId'   => $hu->user->id,
                'UserName' => $hu->user->name,
                'ImageUrl' => url($hu->user->user_image_url),
            ];
        }

        return $this->_render([]);
    
    }

    public function setSignClub(Request $request){
        $this->_validate($request, [
            'ClubId'  => 'required|exists:clubs,id',
        ], ['State' => 201]);
        $clubUser = \App\ClubUser::where('user_id',  $this->auth['user']['id'])->where('club_id', $request->input('ClubId'))->first();
        if(!$clubUser || $clubUser->has_exited){
            return $this->_render([]);
        }
        $today = \Carbon\Carbon::now();
        $attendance = \App\Lib\UserClubAttendance::infoAt( $this->auth['user']['id'], $request->input('ClubId'), $today);
        if($attendance->has_attended){
            return $this->_render([]);
        }
        $todayAttendance = new \App\UserClubAttendance;
        $todayAttendance->user_id = $this->auth['user']['id'];
        $todayAttendance->club_id = $request->input('ClubId');
        $todayAttendance->attended_at = $today;
        $todayAttendance->days        = $attendance->continuous_days + 1;
        if($todayAttendance->save()){
            //event(new \App\Events\UserClubAttend($clubUser->club_id, $clubUser->user_id));
        }
        return $this->_render([]);
    
    }

    public function getListComment(Request $request){
        $this->_validate($request, [
            'ArticleId'  => 'required|exists:articles,id',
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ], ['State' => 201]);
        return $this->_render([]);
    }

    public function setArticleComment(Request $request){
        $this->_validate($request, [
            'ArticleId'  => 'required|exists:articles,id',
        ], ['State' => 201]);
        return $this->_render([]);
    }

    public function getUserFans(Request $request){
        $this->_validate($request, [
            'UserId'  => 'required|exists:articles,id',
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ], ['State' => 201]);
        return $this->_render([]);
    }

    public function getUserFollow(Request $request){
        $this->_validate($request, [
            'UserId'  => 'required|exists:articles,id',
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ], ['State' => 201]);
        
        $query = \App\UserFollower::where('follower_id', $request->input('UserId'));
        $total = $query->count();
        $relations = $query->with('user')->take($request->input('PageSize'))->skip(($request->input('PageIndex')-1)*$request->input('PageSize'))->get();
        $output['FollowList'] = [];
        foreach($relations as $r){
            $output['FollowList'][]=[
                'UserId'    => $r->user_id,
                'UserName'  => $r->user->name,
                'UserImage' => url($r->user->user_image_url),
                'State'     => $r->is_twoway ? 2 : 1,
            ];
        }
        return $this->_render($output);
    }
}
