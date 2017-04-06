<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//$router->pattern('id', '[0-9]+'); //所有id都是数字

$router->resources([
	'member' => 'MemberController',
]);

$router->group(['namespace' => 'Admin', 'prefix' => 'admin', 'middleware' => ['auth', 'role:administrator']], function($router) {
	
	$router->crud([
		'member' => 'MemberController',
	]);
	$router->get('/', 'HomeController@index');

});

$router->get('/', 'HomeController@index');
$router->actions([
	'auth' => ['index', 'login', 'logout', 'choose', 'authenticate-query'],
]);
