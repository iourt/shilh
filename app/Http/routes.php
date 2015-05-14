<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Route::get('/', 'WelcomeController@index');
Route::any('/', function(){
    abort(404);
});
/*
 * http://stackoverflow.com/questions/16520691/consuming-my-own-laravel-api
 * http://stackoverflow.com/questions/29528096/laravel-is-it-possible-to-set-a-controller-dynamically-for-a-route
 * http://stackoverflow.com/questions/28970350/how-to-do-restful-ajax-routes-to-methods-in-laravel-5
 */
/*
Route::post('api/{method}', function(Request $request, $method){
    $class = "ApiController";
    $controller = App::make($class);
    if($controller){
        $controller->callAction($method, array());
    } else {
        abort(404);
    }
});
*/
//Route::post('api/{method}', 'ApiController@_custom_dispatch');
Route::group(['prefix' => 'api', 'middleware' => 'api.validate'], function(){
    $controller='ApiController';
    Route::group([], function() use ($controller) {
        $array = ['index', 'getLogin', 'getCityList', 'setRegInfo'];
        foreach($array as $method){
            Route::post($method, $controller.'@'.$method);
        }
    });
    Route::group(['middleware' => 'api.auth'], function() use ($controller) {
        $array = ['getUserInfo', 'setArticle', 'getListArticle'];
        foreach($array as $method){
            Route::post($method, $controller.'@'.$method);
        }
    });
}); 
Route::get('articleimages/{articleId}/{imageId}.{imageExt}', 'ImageController@article');
