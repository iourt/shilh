<?php namespace App\Http\Controllers;

//use App\Http\Requests;
//use Illuminate\Http\Response;
//use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use App\Http\Requests\CRequest AS Request; 
use App\Http\Controllers\ApiController;

class AdminController extends ApiController {

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
                ->select('articles.*');
        }
        if(!$request->input('Type')){
            $query = new \App\Article;
        
        }
        $total = $query->count();
        $articles = $query->with('images','user', 'user.avatar')->orderBy('articles.id','desc')
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
    public function setArticleCheck(Request $request){
        $this->_validate($request, [
            'ArticleId'  => 'required|array',
            'Type'       => 'in:nCate,yCate,yHome,nHome',
        ]);

        if($request->input('Type') == 'yCate'){
            $articles = \App\Article::whereIn('id', $request->input('ArticleId'))->get();
            \DB::beginTransaction();
            foreach($articles as $article){
                \App\CategoryArticle::firstOrNew(['article_id' => $article->id, 'category_id' => $article->category_id]);
                event(new \App\Events\AdminArticleRecommend('category', $article->id, $article->user_id,  ['category_id' => $article->category_id] ));
            }
            \DB::commit();
        }

        if($request->input('Type') == 'nCate'){
            \App\CategoryArticle::whereIn('id', $request->input('ArticleId'))->delete();
        }
        if($request->input('Type') == 'yHome'){
            $articles = \App\Article::whereIn('id', $request->input('ArticleId'))->get();
            \DB::beginTransaction();
            foreach($articles as $article){
                \App\HomeArticle::firstOrNew(['article_id' => $article->id]);
                event(new \App\Events\AdminArticleRecommend('home', $article->id, $article->user_id));
            }
            \DB::commit();
        }
        if($request->input('Type') == 'nHome'){
            \App\HomeArticle::whereIn('id', $request->input('ArticleId'))->delete();
        }

        return $this->_render($request);
    }

};
