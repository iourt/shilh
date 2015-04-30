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
 */
Route::post('/api/{method}', function($method){
    $class = "JsonController";
    info("method is $method");"
    if(method_exists($class, $method)){
        $controller = App::make($class);
        $controller->callAction($method, array());
    }
});
