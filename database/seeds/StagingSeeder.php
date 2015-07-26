<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class StagingSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    protected $config = [];
    public function run()
    {
        Model::unguard();
        collect(['make_config', 'insert_basic', 'insert_user', 'insert_chat', 
            'insert_article', 'insert_notification', 'update_statistics'])->map(function($m){
                $t = time();
                $this->$m();
                $this->command->info(sprintf("%20s:%5d seconds", $m, time()-$t ));
            });
        /*
        $this->make_config();
        $this->insert_basic();
        $this->command->info(\Carbon\Carbon::now());
        $this->insert_user();
        $this->command->info(\Carbon\Carbon::now());
        $this->insert_chat();
        $this->command->info(\Carbon\Carbon::now());
        $this->insert_article();
        $this->command->info(\Carbon\Carbon::now());
       // $this->insert_text_activity_comment();
        $this->insert_notification();
        $this->command->info(\Carbon\Carbon::now());
        $this->update_statistics();
        $this->command->info(\Carbon\Carbon::now());
         */
    }
    public function insert_basic(){
        \DB::table('cover_images')->delete();
        for($i=1;$i<250;$i++){
            \App\CoverImage::create(['id' => $i, 'filename' => 'default', 'ext' => 'png']);
        }
        \DB::table('banners')->delete();
        for($i=0;$i<5;$i++){
            \App\Banner::create(['filename' => 'default', 'ext' => 'png', 'page'=>config('shilehui.banner_page.home')]);
        }
        for($i=0;$i<3;$i++){
            \App\Banner::create(['filename' => 'default', 'ext' => 'png', 'page'=>config('shilehui.banner_page.guess_like')]);
        }
        \DB::table('categories')->delete();
        $ids = [ 0, 0, 0, 0];
        foreach($this->config['cates'] as $i => $t){
            $name = str_replace('-', '', $t);
            $level = strlen($t) - strlen($name);
            $ids[$level] = $i + 1;
            \App\Category::create(['id' => $i + 1, 'level' => $level, 'name' => $name."-".($i+1), 'parent_id' => $ids[$level-1], 'cover_image_id' => $i + 1 ]);
        }
        \App\Category::chunk(200, function($items){
            \DB::beginTransaction();
            foreach($items as $item){
                $is_leaf = \App\Category::where('parent_id', $item->id)->count() > 0 ? 0 : 1;
                $item->is_leaf = $is_leaf;
                $item->save();
            }
            \DB::commit();
        });


        \DB::table('areas')->delete();
        $ids = [ 0, 0, 0, 0];
        foreach($this->config['areas'] as $i => $t){
            $name = str_replace('-', '', $t);
            $level = strlen($t) - strlen($name);
            $ids[$level] = $i + 1;
            \App\Area::create(['id' => $i + 1, 'level' => $level, 'name' => $name, 'parent_id' => $ids[$level-1] ]);
        }

        \DB::table('clubs')->delete();
        foreach($this->config['clubs'] as $i => $name){
            \App\Club::create(['id' => $i + 1, 'name' => $name, 'cover_image_id' => $i+1 ]);
        }

        \DB::table('activities')->delete();
        foreach($this->config['text_activities'] as $i => $name){
            \App\Activity::create(['id' => $i+1, 'name' => $name, 'type'=>config('shilehui.activity_type.text'), 'cover_image_id' => $i+1 ]);
        }
        foreach($this->config['rich_activities'] as $i => $name){
            \App\Activity::create(['id' => count($this->config['text_activities']) + $i+ 1, 'name' => $name, 'type'=>config('shilehui.activity_type.rich'), 'cover_image_id' => $i+1 ]);
        }

        \DB::table('subjects')->delete();
        foreach($this->config['subjects'] as $i => $name){
            \App\Subject::create(['id' => $i+1, 'name' => $name, 'cover_image_id' => $i+1 ]);
        }
        \DB::commit();

    }
    public function insert_user(){
        $config = $this->config;
        \DB::table('users')->delete();
        \DB::table('user_avatars')->delete();
        foreach(array_chunk(range(1,$config['user_num']), 200) as $users){
            \DB::beginTransaction();
            foreach($users as $i){
                $salt = rand(123456,987654);
                $seed = [];
                foreach($config['user_seed'] as $s){
                    if($i % $s['user_id'] == 0) $seed = $s;
                }
                \App\User::create(['id'=>$i, 'mobile' => $config['user_mobile_base'] + $i, 'name'=> '姓名-'.$i, 'sex' => rand(1,10)%2+1,  
                    'encrypt_pass' => md5($salt."\ti".$config['user_password']), 'salt'=>$salt, 'user_avatar_id' => $i,
                    'article_num'    => rand($seed['article'][0], $seed['article'][1]),
                    'follow_num'     => rand($seed['follow'][0],  $seed['follow'][1]),
                    'fans_num'       => rand($seed['fans'][0],    $seed['fans'][1]),
                    'club_num'       => rand($seed['club'][0],    $seed['club'][1]),
                    ]
                );
                \App\UserAvatar::create(['id' => $i, 'user_id' => $i, 'filename' => 'default', 'ext' => 'png']);
            }
            \DB::commit();
        }


        \DB::table('user_followers')->delete();
        \App\User::chunk(100, function($us) use($config){
            \DB::beginTransaction();
            foreach($us as $u){
                $seed = [];
                foreach($config['user_seed'] as $s){
                    if($u->id % $s['user_id'] == 0) $seed = $s;
                }
                $follows = rand($seed['follow'][0], $seed['follow'][1]);
                while(abs($follows--)){
                    \App\UserFollower::firstOrCreate(['follower_id' => rand(1, $config['user_num']), 'user_id' => $u->id]);
                }
                $fans = rand($seed['fans'][0], $seed['fans'][1]);
                while(abs($fans--)){
                    \App\UserFollower::firstOrCreate(['user_id' => rand(1, $config['user_num']), 'follower_id' => $u->id]);
                }
            }
            \DB::commit();
        });
        \App\UserFollower::chunk(100, function($us) use($config){
            \DB::beginTransaction();
            foreach($us as $u){
                $r=\App\UserFollower::where('user_id', $u->follower_id)->where('follower_id', $u->user_id)->first();
                if($r) {
                    $u->is_twoway=1;
                    $u->save();
                }
            }
            \DB::commit();
        });
        \DB::table('club_users')->delete();
        \App\User::chunk(200, function($us) use($config){
            \DB::beginTransaction();
            foreach($us as $u){
                $seed = [];
                foreach($config['user_seed'] as $s){
                    if($u->id % $s['user_id'] == 0) $seed = $s;
                }
                $clubs = rand($seed['club'][0], $seed['club'][1]);
                while(abs($clubs--)){
                    \App\ClubUser::firstOrCreate(['user_id' => $u->id, 'club_id' => rand(1, count($config['clubs'])+1)]);
                }
            }
            \DB::commit();
        });

    }
    public function insert_chat(){
        $config = $this->config;
        \DB::table('chats')->delete();
        \App\User::chunk(100, function($us) use($config){
            \DB::beginTransaction();
            foreach($us as $u){
                $seed = [];
                foreach($config['user_seed'] as $s){
                    if($u->id % $s['user_id'] == 0) $seed = $s;
                }
                $chats = rand($seed['chats'][0], $seed['chats'][1]);
                while(abs($chats--)){
                    $speak_user_id = rand(1, $config['user_num']);
                    \App\Chat::firstOrCreate(['little_user_id' => min($u->id, $speak_user_id), 
                    'great_user_id' => max($u->id, $speak_user_id),]); 
                }
            }
            \DB::commit();
        });
        \DB::table('chat_messages')->delete();
        \App\Chat::chunk(100, function($us) use($config){
            \DB::beginTransaction();
            $t='《财经》杂志获悉，证监会下发通知，请各证监局约谈近6个月内存在减持本公司股票的大股东及董监高管，减持5亿以下的增持比例不低于累计减持金额的10％；减持5亿元以上的增持比例不低于原减持金额的20％';
            foreach($us as $u){
                $chats = rand(1,50);
                while(abs($chats--)){
                    $user_id = rand(1,10)%2 == 0 ? $u->little_user_id : $u->great_user_id;
                    \App\ChatMessage::create(['user_id' => $user_id, 'chat_id' => $u->id,
                    'content' => substr($t, rand(0,15), rand(1,30)),]); 
                }
            }
            \DB::commit();
        });
    }
    public function insert_article(){
        $config = $this->config;
        \DB::table('articles')->delete();
        \App\User::chunk(200, function($us) use($config){
            $categories = \App\Category::where('is_leaf', 1)->get();
            $subjects   = \App\Subject::all();
            $activities = \App\Activity::where('type', config('shilehui.activity_type.rich'))->get();
            \DB::beginTransaction();
            foreach($us as $u){
                $seed = [];
                foreach($config['user_seed'] as $s){
                    if($u->id % $s['user_id'] == 0) $seed = $s;
                }
                $clubs = \App\ClubUser::where('user_id', $u->id)->get();
                $articles = $u->article_num;
                $catIdx = rand(0,count($categories)-1);
                $catIdx = $catIdx - $catIdx%3;
                while(abs($articles--)){
                    $str="女子闯红灯被协管员铁锤砸头被打女子已脱离生命危险上海受大面积雷雨天气影响 180余架次航班取消小布什弟弟承认伊战是错误：美国不该发动战争奇点大学中国区学员选拔赛赛制公布";
                    \App\Article::create(['title'=>mb_substr($str, rand(0, mb_strlen($str)-5), rand(5,30)), 
                    'user_id'         => $u->id,
                    'user_updated_at' => \Carbon\Carbon::now(),
                    'collection_num'  => rand($seed['article_collection'][0], $seed['article_collection'][1]),
                    'praise_num'      => rand($seed['article_praise'][0], $seed['article_praise'][1]),
                    'comment_num'     => rand($seed['article_comment'][0], $seed['article_comment'][1]),
                    'category_id'     => $categories[$catIdx]->id,
                    'club_id'         => rand(0,9)%2==0 || count($clubs)==0 ? 0 : $clubs[rand(0,count($clubs)-1)]->club_id,
                    'activity_id'     => rand(0,9)%2==0 ? 0 : $activities[rand(0,count($activities)-1)]->id,
                    'subject_id'      => rand(0,9)%2==0 ? 0 : $subjects[rand(0,count($subjects)-1)]->id,
                    ]); 
                }
            }
            \DB::commit();
        });
        \DB::table('article_images')->delete();
        \App\Article::chunk(200, function($items){
            $str="女子闯红灯被协管员铁锤砸头被打女子已脱离生命危险上海受大面积雷雨天气影响 180余架次航班取消小布什弟弟承认伊战是错误：美国不该发动战争奇点大学中国区学员选拔赛赛制公布";
            \DB::beginTransaction();
            foreach($items as $item){
                $count = rand(1,5);
                for($i=0;$i<$count;$i++){
                    \App\ArticleImage::create(['article_id' => $item->id, 'filename' => date('Ymd_His_').$item->user_id.'_'.$i.'.png', 
                        'brief' => 'A'.$item->id.'-'.$i.' x '.substr($str, rand(0,20), rand(20,30)), 'size' => 30000, 'width' => 480, 'height' => 360, 'thumb_width' => 200, 'thumb_height'=>150,'ext' => 'png']);
                }
            }
            \DB::commit();
        });
        \DB::table('category_articles')->delete();
        \App\Article::chunk(200, function($items){
            \DB::beginTransaction();
            foreach($items as $item){
                if(rand(0,9) % 3 !=0 ) continue;
                \App\CategoryArticle::create(['article_id' => $item->id, 'category_id' => $item->category_id]);
            }
            \DB::commit();
        });
        \DB::table('home_articles')->delete();
        \App\Article::chunk(200, function($items){
            \DB::beginTransaction();
            foreach($items as $item){
                if(rand(0,13) % 3 != 0 ) continue;
                \App\HomeArticle::create(['article_id' => $item->id]);
            }
            \DB::commit();
        });
        \DB::table('subject_articles')->delete();
        \App\Article::where('subject_id', '>', 0)->chunk(200, function($items){
            \DB::beginTransaction();
            foreach($items as $item){
                \App\SubjectArticle::create(['article_id' => $item->id, 'subject_id' => $item->subject_id]);
            }
            \DB::commit();
        });
        \DB::table('club_articles')->delete();
        \App\Article::where('club_id', '>', 0)->chunk(200, function($items){
            \DB::beginTransaction();
            foreach($items as $item){
                \App\ClubArticle::create(['article_id' => $item->id, 'club_id' => $item->club_id]);
            }
            \DB::commit();
        });
        \DB::table('activity_articles')->delete();
        \App\Article::where('activity_id', '>', 0)->chunk(200, function($items){
            \DB::beginTransaction();
            foreach($items as $item){
                \App\ActivityArticle::create(['article_id' => $item->id, 'activity_id' => $item->activity_id]);
            }
            \DB::commit();
        });
        \DB::table('article_collections')->delete();
        \App\Article::where('collection_num', '>', 0)->chunk(200, function($items) use($config){
            \DB::beginTransaction();
            foreach($items as $item){
                $uid=rand(1,$config['user_num']);
                for($i=0;$i<$item->collection_num && $uid+$i<$config['user_num'];$i++){
                    \App\ArticleCollection::create(['article_id' => $item->id, 'user_id' => $uid+$i]);
                }
            }
            \DB::commit();
        });
        \DB::table('article_praises')->delete();
        \App\Article::where('praise_num', '>', 0)->chunk(200, function($items) use($config){
            \DB::beginTransaction();
            foreach($items as $item){
                $uid=rand(1,$config['user_num']);
                for($i=0;$i<$item->praise_num && $uid+$i<$config['user_num'];$i++){
                    \App\ArticlePraise::create(['article_id' => $item->id, 'user_id' => $uid+$i]);
                }
            }
            \DB::commit();
        });
        \DB::table('article_comments')->delete();
        \App\Article::where('comment_num', '>', 0)->chunk(200, function($items) use($config){
            \DB::beginTransaction();
            foreach($items as $item){
                $uid=rand(1,$config['user_num']);
                $str="A palindromic number reads the same both ways. The largest palindrome made from the product of two 2-digit numbers is 9009 = 91 × 99. ";
                for($i=0;$i<$item->comment_num && $uid+$i<$config['user_num'];$i++){
                    \App\ArticleComment::create(['article_id' => $item->id, 'user_id' => $uid+$i, 'content' => substr($str, rand(0,30), rand(20,30))]);
                }
            }
            \DB::commit();
        });
    }
    public function insert_notification(){
        \DB::table('notifications')->delete();
        \App\ArticlePraise::with('article')->chunk(200, function($items) {
            \DB::beginTransaction();
            foreach($items as $item){
                \App\Notification::create(['user_id' => $item->article->user_id, 'type' => config('shilehui.notification_type.praise'),
                    'asso_id' => $item->id, 'has_read' => rand(1,10)%2 == 0, 'sender_id' => $item->user_id,
                    'payload' => [] ]);
            }
            \DB::commit();
        });
        \App\ArticleComment::with('article')->chunk(200, function($items) {
            \DB::beginTransaction();
            foreach($items as $item){
                \App\Notification::create(['user_id' => $item->article->user_id, 'type' => config('shilehui.notification_type.comment'),
                    'asso_id' => $item->id, 'has_read' => rand(1,10)%2 == 0, 'sender_id' => $item->user_id,
                    'payload' => ['content' => $item->content] ] );

            }
            \DB::commit();
        });
        \App\Chat::with('messages')->chunk(200, function($items) {
            \DB::beginTransaction();
            foreach($items as $item){
                $m = $item->messages->last();
                $listen_user_id = $m->user_id == $item->great_user_id ?  $item->little_user_id : $item->great_user_id;
                $speak_user_id  = $m->user_id == $item->little_user_id ?  $item->little_user_id : $item->great_user_id;
                \App\Notification::firstOrCreate(['user_id' => $listen_user_id, 'type' => config('shilehui.notification_type.chat'),
                    'asso_id' => $item->id, 'has_read' => rand(1,10)%2 == 0, 'sender_id' => $m->user_id,
                    'payload' => ['content' => $m->content ] ]);
            }
            \DB::commit();
        });
    }
    public function update_statistics(){
        \App\Article::groupBy('category_id')->select('category_id', \DB::raw('count(*) as total') )->get()->each(function($n){
            \App\Category::where('id', $n->category_id)->update(['article_num' => $n->total]); 
        });
        \App\Article::groupBy('club_id')->select('club_id', \DB::raw('count(*) as total') )->get()->each(function($n){
            \App\Club::where('id', $n->club_id)->update(['article_num' => $n->total]); 
        });
        \App\Article::groupBy('subject_id')->select('subject_id', \DB::raw('count(*) as total') )->get()->each(function($n){
            \App\Subject::where('id', $n->subject_id)->update(['article_num' => $n->total]); 
        });
        \App\ClubUser::groupBy('club_id')->select('club_id', \DB::raw('count(*) as total') )->get()->each(function($n){
            \App\Club::where('id', $n->club_id)->update(['user_num' => $n->total]); 
        });
        \App\ArticleCollection::join("articles", "articles.id", "=", "article_collections.article_id")->groupBy('articles.user_id')
            ->select(\DB::raw("articles.id as id"), \DB::raw("count(*) as total"))->get()->each(function($n){
                \App\User::where('id', $n->user_id)->update(['collection_num' => $n->total]);
        });
    }
    public function make_config(){
        $this->config['cates'] = [
            '-教案',
            '--大班',
            '---语言',
            '---美工',
            '---数学',
            '---科学',
            '---音乐',
            '---健康',
            '--中班',
            '---语言',
            '---美工',
            '---数学',
            '---科学',
            '---音乐',
            '---健康',
            '--小班',
            '---语言',
            '---美工',
            '---数学',
            '---科学',
            '---音乐',
            '---健康',
            '--托班',
            '---语言',
            '---美工',
            '---数学',
            '---科学',
            '---音乐',
            '---健康',
            '-计划',
            '--大班',
            '---班务',
            '---学期',
            '---个人',
            '---教学',
            '--中班',
            '---班务',
            '---学期',
            '---个人',
            '---教学',
            '--小班',
            '---班务',
            '---学期',
            '---个人',
            '---教学',
            '--托班',
            '---班务',
            '---学期',
            '---个人',
            '---教学',
            '-总结',
            '--大班',
            '--中班',
            '--小班',
            '--托班',
            '---班务',
            '---学期',
            '---个人',
            '---年终',
            '-专业成长',
            '--大班',
            '---说课评课',
            '---反思',
            '---随笔',
            '---个案分析',
            '---观察记录',
            '---幼儿评语',
            '--中班',
            '---说课评课',
            '---反思',
            '---随笔',
            '---个案分析',
            '---观察记录',
            '---幼儿评语',
            '--小班',
            '---说课评课',
            '---反思',
            '---随笔',
            '---个案分析',
            '---观察记录',
            '---幼儿评语',
            '--托班',
            '---说课评课',
            '---反思',
            '---随笔',
            '---个案分析',
            '---观察记录',
            '---幼儿评语',
            '-玩教具',
            '--泥工',
            '--废旧材料',
            '--纸质',
            '--编织',
            '--布艺',
            '--户外',
            '-环境创设',
            '--盥洗室',
            '--卫生间',
            '--教室一角',
            '--楼梯走廊',
            '--家园栏',
            '--吊饰',
            '--户外环境',
            '-活动区',
            '--大班',
            '---角色区',
            '---语言区',
            '---建筑区',
            '---益智区',
            '---美工区',
            '---科学区',
            '---互动墙',
            '--中班',
            '---角色区',
            '---语言区',
            '---建筑区',
            '---益智区',
            '---美工区',
            '---科学区',
            '---互动墙',
            '--小班',
            '---角色区',
            '---语言区',
            '---建筑区',
            '---益智区',
            '---美工区',
            '---科学区',
            '---互动墙',
            '--托班',
            '---角色区',
            '---语言区',
            '---建筑区',
            '---益智区',
            '---美工区',
            '---科学区',
            '---互动墙',
            '-主题墙',
            '--大班',
            '---节日',
            '---季节',
            '---交通',
            '---生成主题',
            '--中班',
            '---节日',
            '---季节',
            '---交通',
            '---生成主题',
            '--小班',
            '---节日',
            '---季节',
            '---交通',
            '---生成主题',
            '--托班',
            '---节日',
            '---季节',
            '---交通',
            '---生成主题',
            '-素材资源',
            '--人文景观',
            '--生活百科',
            '--宇宙探索',
            '--地球漫谈',
            '--动物',
            '--植物',
            '--人体奥秘',
            '-幼教图库',
            '--简笔画',
            '--节日',
            '--线描',
            '--铅笔',
            '--水粉',
            '--手工粘贴',
            '-家园共育',
            '--家长沟通',
            '--心理特点',
            '--幼小衔接',
            '--入园准备',
            '--保健知识',
            '--科学饮食',
            '-园长之窗',
            '--队伍建设',
            '--管理策略',
            '--园务工作',
            '--园长研修',
            '-儿歌故事',
            '--儿歌',
            '--绕口令',
            '--诗歌',
            '--英文儿歌',
            '--故事',
            '-游戏',
            '--室内',
            '--室外',
            '--亲子',
            '--手指游戏',
            '-特色课程',
            '--思维游戏',
            '--全景数学',
            '--奥尔夫',
            '--蒙台梭利',
            '--英语',
            ];
        $this->config['areas'] =[
            '-河南',
            '--郑州',
            '--开封',
            '--洛阳',
            '-浙江',
            '--杭州',
            '--绍兴',
            '--宁波',
            '--衢州',
            '--金华',
            '--温州',
            '--丽水',
            '--湖州',
            '--嘉兴',
            '--舟山',
            '--台州',
            '-上海',
            '--黄埔',
            '--闵行',
            '--嘉定',
            '--徐汇',
            '-内蒙古',
            '--呼和浩特',
            '--锡林郭勒',
            '--包头',
            ];
        $this->config['clubs'] = [
            '橡皮泥的王国',
            '创意手工',
            '搞怪',
            '孩子气笑话',
            '橡皮泥的王国2号',
            '创意手工2号',
            '搞怪2号',
            '孩子气笑话2号',
            '橡皮泥的王国－3号',
            '创意手工－上海',
            '搞怪－真的',
            '孩子气笑话－3号',
            ];
        $this->config['text_activities'] = [
            '育儿笔记－半岁以内',
            '育儿笔记－一岁',
            '育儿笔记－2-3岁',
            '育儿笔记－4-6岁',
            ];
        $this->config['rich_activities'] = [
            '户外活动1号',
            '早教交流1号',
            '户外活动2号',
            '早教交流2号',
            '户外活动3号',
            '早教交流3号',
            '户外活动4号',
            '早教交流4号',
            ];
        $this->config['subjects'] = [
            '六一专题',
            '入园专题',
            '幼升小专题',
            ];
        $this->config['user_num'] = 200;
        $this->config['user_mobile_base'] = '10012345678';
        $this->config['user_password']    = md5('111111');
        $this->config['user_seed'] = [
            ['user_id' => 1,  
            'article'=>[0,1],  'club' => [0,1], 'follow' => [0,8], 'fans' => [0,5], '_subject' => [0,3], 'chats' => [0,0], '_rich_activity' => [0,2],
            'article_praise' => [0,4], 'article_collection' => [0,8], 'article_comment' => [0,3], 'article_view' => [3,15],
            ],
            ['user_id' => 7,  
            'article'=>[2,5],  'club' => [2,4], 'follow' => [10,15], 'fans' => [30,35], '_subject' => [0,3], 'chats' => [1,6], '_rich_activity' => [0,2],
            'article_praise' => [0,8], 'article_collection' => [0,18], 'article_comment' => [0,10], 'article_view' => [0,25],
            ],
            ['user_id' => 13,  
            'article'=>[15,20], 'club' => [5,9], 'follow' => [22,35], 'fans' => [13,20], '_subject' => [0,3], 'chats' => [15,20], '_rich_activity' => [0,2],
            'article_praise' => [0,40], 'article_collection' => [0,50], 'article_comment' => [0,70], 'article_view' => [0,45],
            ],
            ];
    }



    // us DB::statement($sql);
}
