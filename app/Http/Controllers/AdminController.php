<?php namespace App\Http\Controllers;

//use App\Http\Requests;
//use Illuminate\Http\Response;
//use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use App\Http\Requests\CRequest AS Request; 
use App\Http\Controllers\ApiController;

class AdminController extends ApiController {

    public function getLogin(Request $request){
        $user = \App\User::where('mobile', $request->input('Phone'))->first();
        if(empty($user)){
            return $this->_render($request, false);
        }
        $isAdmin = \App\UserRole::where('user_id', $user->id)->where('role_type', config('shilehui.role.admin'))->count();
        if(!$isAdmin){
            return $this->_render($request, false);
        }

        return parent::getLogin($request); 
    } 

    public function getArticleList(Request $request){
        $this->_validate($request, [
            'CateId'     => 'exists:categories,id',
            'Type'       => 'in:nCate,yCate,yHome',
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
            ]);
        $query = null;
        $cateIds = null;
        if($request->input('CateId')){
            $cateIds = \App\Lib\Category::getDescendantsOf($request->input('CateId'))->lists('id');
        }
        if($request->input('Type') == 'yCate'){
            $query = \App\Article::join('category_articles', 'articles.id', '=', 'category_articles.article_id');
            if($request->input('CateId')){
                $query = $query->whereIn('category_articles.category_id', $cateIds);
            }
            $query = $query->select('articles.*');
        
        }
        if($request->input('Type') == 'yHome'){
            $query = \App\Article::join('home_articles', 'articles.id', '=', 'home_articles.article_id');
            if($request->input('CateId')){
                $query = $query->whereIn('articles.category_id', $cateIds);
            }
            $query = $query->select('articles.*');
        }
        if($request->input('Type') == 'nCate'){
            $query = \App\Article::leftJoin('category_articles', 'articles.id', '=', 'category_articles.article_id')
                ->whereNull('category_articles.article_id');
            if($request->input('CateId')){
                $query = $query->whereIn('articles.category_id', $cateIds);
            }
            $query = $query->select('articles.*');
        }
        if(!$request->input('Type')){
            $query = new \App\Article;
            if($request->input('CateId')){
                $query = $query->whereIn('articles.category_id', $cateIds);
            }
        
        }
        $total = $query->count();
        $articles = $query->with('images','user', 'user.avatar', 'category_article', 'home_article')->orderBy('articles.id','desc')
            ->skip( ($request->input('PageIndex') - 1)*$request->input('PageSize'))
            ->take($request->input('PageSize'))->get();
        $this->output = ['ArticleList' => [], 'Total' => $total ];
        foreach($articles as $article){
            $item = ['ArticleId' => $article->id, 'TotalCollect' => $article->collection_num, 'Images' => [], 'Author' => [], 'CategoryList' => [], 
                'CreateTime' => $article->created_at->toDateTimeString(),
                'UpdateTime' => $article->updated_at->toDateTimeString(),
            ];
            foreach($article->images as $image){
                $item['Images'][] = \App\Lib\Image::renderImage($image,'thumb');
            }
            $item['Author']   = \App\Lib\User::renderAuthor($article->user);
            $item['CategoryList'] = \App\Lib\Category::renderBreadcrumb($article->category_id);
            $item['EditState'] = ( empty($article->category_article) || $article->category_article->category_id!= $article->category_id ) ? 'nCate' : 'yCate';
            $item['HomeState'] = empty($article->home_article)  ? 'nHome' : 'yHome';
            $this->output['ArticleList'][]=$item;
        }
        return $this->_render($request);
    }
    public function setArticleCheck(Request $request){
        $this->_validate($request, [
            'ArticleId'  => 'required|array',
            'Type'       => 'in:nCate,yCate,yHome,nHome',
        ]);

        if($request->input('Type') == 'yCate'){
            $articles = \App\Article::whereIn('id', $request->input('ArticleId'))->get();
            \DB::beginTransaction();
            foreach($articles as $article){
                \App\CategoryArticle::firstOrNew(['article_id' => $article->id, 'category_id' => $article->category_id])->save();
                event(new \App\Events\AdminArticleRecommend('category', $article->id, $article->user_id,  ['category_id' => $article->category_id] ));
            }
            \DB::commit();
        }

        if($request->input('Type') == 'nCate'){
            \App\CategoryArticle::whereIn('article_id', $request->input('ArticleId'))->delete();
        }
        if($request->input('Type') == 'yHome'){
            $articles = \App\Article::whereIn('id', $request->input('ArticleId'))->get();
            \DB::beginTransaction();
            foreach($articles as $article){
                \App\HomeArticle::firstOrNew(['article_id' => $article->id])->save();
                event(new \App\Events\AdminArticleRecommend('home', $article->id, $article->user_id));
            }
            \DB::commit();
        }
        if($request->input('Type') == 'nHome'){
            \App\HomeArticle::whereIn('article_id', $request->input('ArticleId'))->delete();
        }

        return $this->_render($request);
    }

    public function getAdminList(Request $request){
        $users = \App\User::join('user_roles', 'users.id', '=', 'user_roles.user_id')->where('user_roles.role_type', config('shilehui.role.admin'))
            ->select('users.*')->get();

        $this->output['UserList'] = [];
        foreach($users as $u){
            $this->output['UserList'][] = [
                'UserId'   => $u->id,
                'UserName' => $u->name,
            ];
        }

        return $this->_render($request);
    }

    public function setRole(Request $request){
        $this->_validate($request, [
            'UserName'  => 'required_without:UserId|exists:users,name',
            'UserId'    => 'required_without:UserName|exists:users,id',
            'Type'      => 'in:admin,user',
        ]);
        $user = null;
        if($request->input('UserName')){
            $user = \App\User::where('name', $request->input('UserName'))->first();
        }
        if($request->input('UserId')){
            $user = \App\User::where('id', $request->input('UserId'))->first();
        }
        if(empty($user)){
            return $this->_render($request, false);
        }
        \DB::beginTransaction();
        try {
            \App\UserRole::where('user_id', $user->id)->delete();
            if($request->input('Type') == 'admin'){
                \App\UserRole::create([ 'user_id'   => $user->id, 'role_type' => config('shilehui.role.admin'), ]);
            }
        } catch (Exception $e){
            \DB::rollback();
            return $this->_render($request, false);
        }
        \DB::commit();
        if($user->id == $request->crUserId()){
            \Log::info('[AUTH]should remove auth of [userid '.$user->id.']');
        //    $auth = new \App\Lib\Auth('API', $request->crUserId());
        //    $auth->removeUserAuth();
        }
        return $this->_render($request);
    }

    public function getUserList(Request $request){
        $this->_validate($request, [
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ]);
        $query = new \App\User;
        $total = $query->count();
        $users = $query->with('avatar')->orderBy('id','desc')
            ->skip( ($request->input('PageIndex') - 1)*$request->input('PageSize'))
            ->take($request->input('PageSize'))->get();
        $this->output['Total'] = $total;
        $this->output['UserList'] = [];
        foreach($users as $user){
            $this->output['UserList'][] = [
                'UserId'    => $user->id,
                'UserImage' => empty($user->avatar) ? '' : url($user->avatar->url),
                'UserName'  => $user->name,
                'Phone'     => $user->mobile,
                'Sex'       => $user->sex,
                'Job'       => $user->job_id,
                'Area'      => $user->area_id,
                'Exper'     => $user->exp_num,
                'CreateTime' => $user->created_at->toDateTimeString(),
            ];
        }
        return $this->_render($request);
    }

    public function getCommentList(Request $request){
        $this->_validate($request, [
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ]);
        $query =  new \App\ArticleComment;

        $total = $query->count();
        $comments = $query->with('user', 'user.avatar')->orderBy('id','desc')
            ->skip( ($request->input('PageIndex') - 1)*$request->input('PageSize'))
            ->take($request->input('PageSize'))->get();
        $this->output['Total'] = $total;
        $this->output['CommentList'] = [];
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

    public function addClub(Request $request){
        $this->_validate($request, [
            'ClubName' => 'required|min:2',
            'ImageUrl' => 'required|min:20',
            'Description' => 'required',
            'Letter'      => 'alpha|min:1|max:1',
            'CateId'      => 'exists:categories,id'
        ]);

        $imageData  = \App\Lib\Image::decodeAndSaveAsTmp($request->input('ImageUrl'), $request->crUserId());
        try{
            \DB::beginTransaction();
            $cover = \App\CoverImage::create([
                'filename' => $imageData['name'],
                'origname' => '',
                'ext'      => $imageData['ext'],
            ]);
            $club = \App\Club::create([
                'name' => $request->input('ClubName'),
                'cover_image_id' => $cover->id,
                'brief'  => $request->input('Description'),
                'letter' => $request->input('Letter'),
                'to_category_id' => $request->input('CateId'),
            ]);
            \DB::commit();
        } catch(Exception $e){
            \DB::rollback();
            return $this->_render($request, false, ['Err' => $e]);
        }
        \App\Lib\Image::moveToDestination($cover->filename, $cover->ext);
        return $this->_render($request);
    }
    public function addActivity(Request $request){
        $this->_validate($request, [
            'ActivityName'   => 'required|min:1',
            'ActivityType'   => 'required|in:'.implode(",", config('shilehui.activity_type')),
            'ImageUrl'       => 'required|min:20',
            'CateId'         => 'required|exists:categories,id',
        ]);
        $imageData  = \App\Lib\Image::decodeAndSaveAsTmp($request->input('ImageUrl'), $request->crUserId());
        try{
            \DB::beginTransaction();
            $cover = \App\CoverImage::create([
                'filename' => $imageData['name'],
                'origname' => '',
                'ext'      => $imageData['ext'],
            ]);
            $activity = \App\Activity::create([
                'name' => $request->input('ActivityName'),
                'cover_image_id' => $cover->id,
                'brief'          => $request->input('Intro'),
                'description'    => $request->input('Description'),
                'alias'  => $request->input('ActivityLabel'),
                'type'   => $request->input('ActivityType'),
                'to_category_id' => $request->input('CateId'),
            ]);
            \DB::commit();
        } catch(Exception $e){
            \DB::rollback();
            return $this->_render($request, false);
        }
        \App\Lib\Image::moveToDestination($cover->filename, $cover->ext);
        return $this->_render($request);
    }
    public function addSubject(Request $request){
        $this->_validate($request, [
            'LongName' => 'required|min:1',
            'ImageUrl' => 'required|min:20',
            'ClubId'   => 'exists:clubs,id',
        ]);
        $imageData  = \App\Lib\Image::decodeAndSaveAsTmp($request->input('ImageUrl'), $request->crUserId());
        try{
            \DB::beginTransaction();
            $cover = \App\CoverImage::create([
                'filename' => $imageData['name'],
                'origname' => '',
                'ext'      => $imageData['ext'],
            ]);
            $subject = \App\Subject::create([
                'name' => $request->input('LongName'),
                'cover_image_id' => $cover->id,
                'brief'          => $request->input('Description'),
                'alias'  => $request->input('ShortName'),
                'club_id'   => $request->input('ClubId'),
            ]);
            \DB::commit();
        } catch(Exception $e){
            \DB::rollback();
            return $this->_render($request, false);
        }
        \App\Lib\Image::moveToDestination($cover->filename, $cover->ext);
        $this->_validate($request, [
            'PageIndex'  => 'required|integer',
            'PageSize'   => 'required|integer',
        ]);
        return $this->_render($request);
    }

    public function setArticleType(Request $request){
        $this->_validate($request, [
            'ArticleId' => 'required|array',
            'Type'      => 'required|in:club,subject,activity',
            'Id'        => 'required|integer',
        ]);
        $des = null;
        if($request->input('Type') == 'club'){
            $des = \App\Club::find($request->input('Id'));
        } else if($request->input('Type') == 'subject'){
            $des = \App\Subject::find($request->input('Id'));
        } else if($request->input('Type') == 'activity'){
            $des = \App\Activity::find($request->input('Id'));
        }
        if(empty($des)) {
            return $this->_render($request, false);
        }
        $articles = \App\Article::whereIn('id', $request->input('ArticleId'))->get();
        if(count($articles) != count($request->input('ArticleId'))){
            return $this->_render($request, false);
        }
        try{
            \DB::BeginTransaction();
            foreach($articles as $article){
                if($request->input('Type') == 'club'){
                    $this->moveArticleIntoClub($article->id, $des->id);
                } else if($request->input('Type') == 'subject'){
                    $this->moveArticleIntoSubject($article->id, $des->id);
                } else if($request->input('Type') == 'activity'){
                    if($des->type == config('shilehui.activity_type.text')){
                        $this->moveArticleIntoTextActivity($article->id, $des->id);
                        break;
                    } else {
                        $this->moveArticleIntoRichActivity($article->id, $des->id);
                    }
                }
            }
            \DB::commit();
        } catch(Exception $e){
            \DB::rollback();
            return $this->_render($request, false);
        }
        return $this->_render($request);
    }
    protected function moveArticleIntoClub($articleId, $clubId){
        \App\ClubArticle::create(['article_id' => $articleId, 'club_id' => $clubId]);
        \App\Article::where('id', $articleId)->update(['club_id' => $clubId]);
    }
    protected function moveArticleIntoSubject($articleId, $subjectId){
        \App\SubjectArticle::create(['article_id' => $articleId, 'subject_id' => $subjectId]);
        \App\Article::where('id', $articleId)->update(['subject_id' => $subjectId]);
    }
    protected function  moveArticleIntoRichActivity($articleId, $activityId){
        \App\ActivityArticle::create(['article_id' => $articleId, 'activity_id' => $activityId]);
        \App\Article::where('id', $articleId)->update(['activity_id' => $activityId]);
    }
    protected function  moveArticleIntoTextActivity($articleId, $activityId){
        \App\ActivityArticle::where('activity_id', $activityId)->delete();
        \App\Article::where('activity_id', $activityId)->update(['activity_id' => 0]);
        \App\ActivityArticle::create(['article_id' => $articleId, 'activity_id' => $activityId]);
        \App\Article::where('id', $articleId)->update(['activity_id' => $activityId]);
    }












};
