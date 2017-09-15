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

Route::get('/', function () {
    return view('welcome');
});
// Route to create a new role
Route::post('role', 'JwtAuthenticateController@createRole');
// Route to create a new permission
Route::post('permission', 'JwtAuthenticateController@createPermission');
// Route to assign role to user
Route::post('assign-role', 'JwtAuthenticateController@assignRole');
// Route to attache permission to a role
Route::post('attach-permission', 'JwtAuthenticateController@attachPermission');
Route::group(
    [
        'namespace' => 'Api\V1\User',
        'prefix' => 'api',
    ], function(){       
        Route::post('User/Authenticate', ['uses'=>'UserController@authenticate']);
        Route::post('User/logout', ['uses'=>'UserController@logout']);
    });

Route::group(['prefix' => 'api', 'middleware' => ['ability:admin,create-users']], function()
{
        Route::get('users', 'JwtAuthenticateController@index');

});

