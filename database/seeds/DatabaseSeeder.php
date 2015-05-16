<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		$this->call('ConfigSeriesTableSeeder');
		$this->call('UserSeriesTableSeeder');
		$this->call('ArticleSeriesTableSeeder');
	}

}
class ConfigForSeeder {
    public $cate1, $cate2, $cate3, $job, $area1, $area2, $area3, $club,
        $activity_text, $activity_rich, $subject, $user, $article;

    public function __construct(){
        if(1){
            $r = 10;
            $this->cate1 = range(1, $r);
            $this->cate2 = [];
            $this->cate3 = [];
            foreach($this->cate1 as $id){
                $x = rand(1, 10);
                $this->cate2[$id] = range($r+1, $r+$x);
                $r +=$x;  
            }
            foreach($this->cate2 as $val){
                foreach($val as $id){
                    $x = rand(1, 10);
                    $this->cate3[$id] = range($r+1, $r+$x);
                    $r +=$x;
                }
            }
        }
        if(1){
            $r = 30;
            $this->area1 = range(1, $r);
            $this->area2 = [];
            $this->area3 = [];
            foreach($this->area1 as $id){
                $x = rand(10, 20);
                $this->area2[$id] = range($r+1, $r+$x);
                $r +=$x;  
            }
            foreach($this->area2 as $val){
                foreach($val as $id){
                    $x = rand(4, 10);
                    $this->area3[$id] = range($r+1, $r+$x);
                    $r +=$x;
                }
            }
        }
        $this->job  = 30;
        $this->club = 40; 
        $this->activity_text = 7;
        $this->activity_rich = 33;
        $this->subject = 20;
        $this->user    = 200;
        $this->article = 1200;
    }
}


class UserSeriesTableSeeder extends Seeder {

    public function run()
    {
        $config = new \ConfigForSeeder();
        \DB::table('users')->delete();
        for($i=1;$i<=$config->user;$i++){
            $salt = rand(10000000,99999999);
            \App\User::create(['id'=>$i, 'mobile' => 1367771111+$i, 'name'=> '姓名-'.$i, 'sex' => rand(1,10)%2==0 ? 'female' : 'male',  
                'encrypt_pass' => md5($salt.'\t111111'), 'salt'=>$salt, 'user_image_id' => rand(1,10)%2 * $i ]
            );
        }
        
        foreach(range(0,10) as $i){
            $filename   = '20140515_1111_'.$i.'.png';
            $folder     = \App\Lib\Image::getPathOfName($filename);
            $file  = sprintf("%s/app/%s/%s", storage_path(), $folder, $filename);
            @mkdir(sprintf("%s/app/%s", storage_path(), $folder), 0777, true);
            $im = imagecreate(100,100);
            $background_color = imagecolorallocate($im, 0, 0, 0);
            $text_color = imagecolorallocate($im, 233, 14, 91);;
            imagestring($im, 3, 5, 5, '头像-'.$i, $text_color);
            imagepng($im, $file);
            imagedestroy($im);
        }
        
        \DB::table('user_images')->delete();
        \App\User::where('user_image_id', '>', 0)->get()->each(function($u){
            $filename   = '20140515_1111_'.($u->id%10).'.png';
            \App\UserImage::create(['id' => $u->user_image_id, 'user_id' => $u->id, 'filename' => $filename]);
        });
        \DB::table('user_relations')->delete();
        \App\User::all()->each(function($u) use($config){
            $fans =  rand(0, $u->id%10 == 0 ? 100 : 5); 
            $r = rand(0, $config->user);
            while($fans){
                $buddy_id = rand(1, $config->user);
                if($buddy_id == $u->id) continue;
                \App\UserRelation::firstOrCreate(['user_id' => $u->id, 'buddy_user_id' => $buddy_id, 'relation' => 'fan']);
                $fans--;
            }
            $follows = rand(0, 20);
            while($follows){
                $buddy_id = rand(1, $config->user);
                if($buddy_id == $u->id) continue;
                \App\UserRelation::firstOrCreate(['user_id' => $u->id, 'buddy_user_id' => $buddy_id, 'relation' => 'follow']);
                $follows--;
            }
        });
    }
}


class ConfigSeriesTableSeeder extends Seeder {
    public function run() {
        $config = new ConfigForSeeder();
        \DB::table('categories')->delete();
        foreach($config->cate1 as $id){
            \App\Category::create(['id' => $id, 'level' => 1, 'name' => "一级-1-0-$id", 'parent_id' => 0]);
        }
        foreach($config->cate2 as $pid => $ids){
            foreach($ids as $id){
                \App\Category::create(['id' => $id, 'level' => 2, 'name' => "二级-2-$pid-$id", 'parent_id' => $pid]);
            }
        }
        foreach($config->cate3 as $pid => $ids){
            foreach($ids as $id){
                \App\Category::create(['id' => $id, 'level' => 3, 'name' => "三级-3-$pid-$id", 'parent_id' => $pid]);
            }
        }

        \DB::table('areas')->delete();
        foreach($config->area1 as $id){
            \App\Area::create(['id' => $id, 'level' => 1, 'name' => "省-1-0-$id", 'parent_id' => 0]);
        }
        foreach($config->area2 as $pid => $ids){
            foreach($ids as $id){
                \App\Area::create(['id' => $id, 'level' => 2, 'name' => "市-2-$pid-$id", 'parent_id' => $pid]);
            }
        }
        foreach($config->area3 as $pid => $ids){
            foreach($ids as $id){
                \App\Area::create(['id' => $id, 'level' => 3, 'name' => "区-3-$pid-$id", 'parent_id' => $pid]);
            }
        }

        \DB::table('jobs')->delete();
        foreach(range(1,$config->job) as $id){
            \App\Job::create(['id' => $id, 'seq_id'=>$id, 'name' => '职位-'.$id]);
        }

        \DB::table('clubs')->delete();
        foreach(range(1,$config->club) as $id){
            \App\Club::create(['id' => $id, 'name' => '圈子-'.$id]);
        }

        \DB::table('activities')->delete();
        foreach(range(1,$config->activity_text) as $id){
            \App\Activity::create(['id' => $id, 'title' => '文字活动-'.$id, 'type'=>1]);
        }
        foreach(range($config->activity_text+1, $config->activity_rich) as $id){
            \App\Activity::create(['id' => $id, 'title' => '图文活动-'.$id, 'type'=>2]);
        }

        \DB::table('subjects')->delete();
        foreach(range(1,$config->subject) as $id){
            \App\Subject::create(['id' => $id, 'name' => '专题-'.$id]);
        }
    }
}
class ArticleSeriesTableSeeder extends Seeder {
    public function run() {
        $config = new ConfigForSeeder();
        $categories = [];
        $activities = range($config->activity_text+1, $config->activity_rich);
        $clubs      = range(1, $config->club) ;
        $subjects   = range(1, $config->subject);

        foreach($config->cate3 as $cate){
            $categories = array_merge($categories, $cate);
        }
        for($i=0;$i<count($categories)/2;$i++){
            $categories[]=0;
        }
        for($i=0;$i<count($activities)/2;$i++){
            $activities[]=0;
        }
        for($i=0;$i<count($clubs)/2;$i++){
            $clubs[]=0;
        }
        for($i=0;$i<count($subjects)/2;$i++){
            $subjects[]=0;
        }
        shuffle($categories);
        shuffle($clubs);
        shuffle($activities);
        shuffle($subjects);



        \DB::table('articles')->delete();
        for($i=1;$i<$config->article;$i++){
            $str="女子闯红灯被协管员铁锤砸头被打女子已脱离生命危险上海受大面积雷雨天气影响 180余架次航班取消小布什弟弟承认伊战是错误：美国不该发动战争奇点大学中国区学员选拔赛赛制公布";
            \App\Article::create(['id'=>$i, 'title'=>mb_substr($str, rand(0, mb_strlen($str)-5), rand(5,30)), 
                'user_id'     => rand(1, $config->user-1),
                'category_id' => $categories[rand(1,count($categories))-1], 
                'activity_id' => $activities[rand(1,count($activities))-1], 
                'club_id'     => $clubs[rand(1,count($clubs))-1],
                'subject_id'  => $subjects[rand(1,count($subjects))-1],
                'collection_num' => rand(0, $i%10==1 ? 50 : 5),
                'praise_num'     => rand(0, $i%10==1 ? 20 : 2),
                ]); 
        }


        \DB::table('article_images')->delete();
        \App\Article::all()->each(function($item){
            $count = rand(1,3);
            for($i=0;$i<$count;$i++){
                \App\ArticleImage::create(['article_id' => $item->id, 'filename' => date('Ymd_His_').$item->user_id.'_'.$i.'.png', 
                    'brief' => 'A'.$item->id.'-'.$i.' x 太好玩了', 'size' => 30000, 'width' => 480, 'height' => 360, 'ext' => 'png']);
            }
        });
        \DB::table('category_articles')->delete();
        \App\Article::where('category_id', '>', 0)->get()->each(function($item){
            \App\CategoryArticle::create(['article_id' => $item->id, 'category_id' => $item->category_id]);
        });
        \DB::table('subject_articles')->delete();
        \App\Article::where('subject_id', '>', 0)->get()->each(function($item){
            \App\SubjectArticle::create(['article_id' => $item->id, 'subject_id' => $item->subject_id]);
        });
        \DB::table('club_articles')->delete();
        \App\Article::where('club_id', '>', 0)->get()->each(function($item){
            \App\ClubArticle::create(['article_id' => $item->id, 'club_id' => $item->club_id]);
        });
        \DB::table('activity_articles')->delete();
        \App\Article::where('activity_id', '>', 0)->get()->each(function($item){
            \App\ActivityArticle::create(['article_id' => $item->id, 'activity_id' => $item->activity_id]);
        });
        \DB::table('article_collections')->delete();
        \App\Article::where('collection_num', '>', 0)->get()->each(function($item) use($config){
            $uid=rand(1,$config->user);
            for($i=0;$i<$item->collection_num && $uid<$config->user;$i++){
                \App\ArticleCollection::create(['article_id' => $item->id, 'user_id' => $uid+$i]);
            }
        });
        \DB::table('article_praises')->delete();
        \App\Article::where('praise_num', '>', 0)->get()->each(function($item) use($config){
            $uid=rand(1,$config->user);
            for($i=0;$i<$item->praise_num && $uid<$config->user;$i++){
                \App\ArticlePraise::create(['article_id' => $item->id, 'user_id' => $uid+$i]);
            }
        });


    }
}

