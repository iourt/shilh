<?php
namesapce App\Lib;
class User {
    public static function getUserStat($userId){
        $stats = [];
        $user = \App\User::find($userId);
        if(empty($user)){
            throw new Exception();
        }
        $types = config('shilehui.user_update_types');
        $stats['article_num'] = \App\Article::where('user_id', $userId)->count();
        collect(['category', 'club', 'collection'])->each(function($type){
            $stats['latest_'.$type] = [];
            \App\UserRecentUpdate::where('user_id', $userId)->
                where('type', $types[$type])->orderBy('updated_at')
                ->take(6)->get()->each(function($item){
                    $stats['latest_'.$type][] = $item->type_id;
                });
        });
        $stats['latest_category']   = \App\Category::whereIn('id', $stats['latest_category'])->get();
        $stats['latest_collection'] = \App\Category::whereIn('id', $stats['latest_collection'])->get();
        $stats['latest_club']       = \App\Club::whereIn('id', $stats['latest_club'])->get();
        return $stats;


        /*
        $stats['article_num'] = \App\Article::where('user_id', $userId)->count();
        $stats['latest_categories'] = \App\Article::where('user_id', $userId)->select('categeory_id', 'max(created_at) as cate_created_at')->groupBy('categroy_id')->orderBy('cate_created_at')->get();
        $stats['latest_clubs'] = DB::table('club_articles as ca ')->join("articles as a", "a.id", "=", "ca.article_id");
         */
    }
}
