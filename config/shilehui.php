<?php
return [
'area_level' => [
    'province' => 1,
    'city'     => 2,
    'county'   => 3,
],
'sex'          => ['male', 'female'],
'article_type' => [
    'normal'   => 1,
    'activity' => 2,
    'club'     => 3,
    'subject'  => 4,
],
'user_update_types' => [
    'collection_category' => 2,
    'club'                => 3,
    'article_category'    => 1,
],
'default_image' => [
    'article'       => 'images/notfound3.png',
    'article_thumb' => 'images/notfound3.png',
    'user'    => 'images/user.png',
    'cover'   => 'images/cover.png',
    'banner'  => 'images/banner.png',
],
'dimension'     => [
    'article_thumb_with' => 200,
],
'sms_api' => [
    'gateway' => 'http://sdk.kuai-xin.com:8888/sms.aspx?action=send'
        .'&userid='.env('KUAIXIN_USERID')
        .'&account='.env('KUAIXIN_ACCOUNT')
        .'&password='.env('KUAIXIN_PASSWORD')
        .'&mobile={mobile}&content={content}'.env('KUAIXIN_SUFFIX')
        .'&sendTime={sendtime}',
],
'activity_type' => [
    'text'      => 1,
    'rich'      => 2,
],
'banner_page' => [
    'home'       => 1,
    'guess_like' => 2,
],
'role' => [
    'guest' => 0,//when not logined
    'user'  => 1,//when has login
    'admin' => 2,//when exists in user_role
    'ban'   => 3,//when exists in user_role
],
'verify_code' => [
    'fetch_password' => [
        'id'     => 1,
        'seconds'=> 60*12,
    ],
],
'notification_type' => [
    'notice'     => 1,
    'praise'     => 2,
    'comment'    => 3,
    'collection' => 4,
    'chat'       => 5,
    'follow'     => 6,
    'welcome'    => 7,
    'system'     => 8,
    'friend_register' => 9,
],

'notice_scope' => [
    'all'    => 1,
    'admin'  => 2,
    'single' => 3,
],
'jobs' => [ '1', '2', '3', '4', ],
'exp_action' => [
    'by_self' => [
        'register'  => ['id' => 1, 'exp' => 5,],
        'login'     => ['id' => 2, 'exp' => 1,],
        'attend'    => ['id' => 3, 'exp' => 1,],
        'post'      => ['id' => 4, 'exp' => 1,],
        'delete'    => ['id' => 5, 'exp' => -1,],
    ],
    'by_user' => [
        'collect'   => ['id' => 6, 'exp' => 1,],
        'praise'    => ['id' => 7, 'exp' => 1,],
    ], 
    'by_admin' => [
        'recommend' => ['id' => 8, 'exp' => 1,],
        'delete'    => ['id' => 9, 'exp' => -3,],
    ],
],






];
