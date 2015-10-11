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
                ->whereNull('category_articles.article_id') 
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
};
