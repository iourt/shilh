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
],
'dimension'     => [
    'article_thumb_with' => 200,
],
'sms_api' => [
    'gateway' => 'http://sdk.kuai-xin.com:8888/sms.aspx?action=send&userid='
        .env('KUAIXIN_USERID').'&account='
        .env('KUAIXIN_ACCOUNT').'&password='
        .env('KUAIXIN_PASSWORD')
        .'&mobile={mobile}&content={content}'
        .env('KUAIXIN_SUFFIX').'&sendTime={sendtime}',
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
    'guest' => 0,
    'user'  => 1,
    'admin' => 2,
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
],
'notice_scope' => [
    'all'    => 1,
    'admin'  => 2,
    'single' => 3,
],
];
