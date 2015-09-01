<?php namespace App\Lib;
class User {
    public static function getUserStat($userId, $needRefresh = false){
        $stats = [];
        $user = \App\User::find($userId);
        if(empty($user)){
            throw new Exception();
        }
        if($needRefresh){
            \App\Lib\User::refreshUserRecentUpdate($user->id);
        }
        $types = config('shilehui.user_update_types');
        foreach(['article_category', 'club', 'collection_category'] as $type ){
            $stats['latest_'.$type] = [];
            $arr = \App\UserRecentUpdate::where('user_id', $userId)->where('type', $types[$type])->orderBy('updated_at')->take(6)->get();
            foreach($arr as $item){
                $stats['latest_'.$type][] = $item->type_id;
            }
        }
        $stats['latest_article_category']   = \App\Category::with('cover_image')->whereIn('id', $stats['latest_article_category'])->get();
        $stats['latest_collection_category'] = \App\Category::with('cover_image')->whereIn('id', $stats['latest_collection_category'])->get();
        $stats['latest_club']       = \App\Club::with('cover_image')->whereIn('id', $stats['latest_club'])->get();

        return $stats;

    }

    public static function refreshUserRecentUpdate($userId){
        $types = config('shilehui.user_update_types');

        $user = \App\User::find($userId);
        $user->article_num    = \App\Article::where('user_id', $userId)->count();
        $user->collection_num = \App\ArticleCollection::where('user_id', $userId)->count();
        $user->club_num      = \App\ClubUser::where('user_id', $userId)->where('has_exited',0)->count();
        $user->save();

        $arr = \App\Article::where('user_id', $user->id)->select(\DB::raw("max(created_at) as created_at, id as article_id, category_id"))->groupBy("category_id")->get();
        \DB::beginTransaction();
        \App\UserRecentUpdate::where('user_id', $user->id)->where('type', config('shilehui.user_update_types.article_category'))->delete();
        foreach($arr as $c){
            if(!$c->category_id) continue;
            \App\UserRecentUpdate::create(['user_id' => $user->id, 'type' => config('shilehui.user_update_types.article_category'), 'type_id' => $c->category_id, 'article_id' => $c->article_id, 'updated_at' => $c->created_at]);
        }
        \DB::commit();

        $arr = \App\Article::join("article_collections", "articles.id","=","article_collections.article_id")->where('articles.user_id', $user->id)
            ->select(\DB::raw("max(article_collections.created_at), article_collections.article_id, articles.category_id"))->groupBy("articles.category_id")->get();
        \DB::beginTransaction();
        \App\UserRecentUpdate::where('user_id', $user->id)->where('type', config('shilehui.user_update_types.collection_category'))->delete();
        foreach($arr as $c){
            if(!$c->category_id) continue;
            \App\UserRecentUpdate::create(['user_id' => $user->id, 'type' => config('shilehui.user_update_types.collection_category'), 'type_id' => $c->category_id, 'article_id' => $c->article_id, 'updated_at' => $c->created_at]);
        }
        \DB::commit();

        $arr = \App\ClubUser::where('user_id', $user->id)->where('has_exited',0)->orderBy('updated_at', 'desc')->get();
        \DB::beginTransaction();
        \App\UserRecentUpdate::where('user_id', $user->id)->where('type', config('shilehui.user_update_types.club'))->delete();
        foreach($arr as $c){
            if(!$c->club_id) continue;
            \App\UserRecentUpdate::create(['user_id' => $user->id, 'type' => config('shilehui.user_update_types.club'), 'type_id' => $c->club_id, 'article_id' => 0, 'updated_at' => $c->created_at]);
        }
        \DB::commit();
    }

    public static function renderAuthor($user){
        $arr=[];
        $arr['UserId']   = empty($user) ? '' : $user->id;
        $arr['ImageUrl'] = empty($user) || empty($user->avatar) ? '' : url($user->avatar->url);
        $arr['UserImage'] = $arr['ImageUrl']; 
        $arr['UserName'] = empty($user) ? '' : $user->name;
        return $arr;
    }
}
