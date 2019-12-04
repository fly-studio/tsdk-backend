<?php

$router->api('v1', function($router) {

	$router->group(['prefix' => 'sdk'], function($router){
		/**
		 * APP
		 */
		$router->post('launch', 'AppController@index');
		$router->post('start', 'AppController@start');
		$router->post('pause', 'AppController@pause');
		$router->post('exception', 'AppController@exception');
		$router->post('crash', 'AppController@crash');
		$router->post('tick', 'AppController@tick');

		/**
		 * User
		 */
		$router->post('register', 'UserController@register');
		$router->post('login', 'UserController@login');
		$router->post('verify', 'UserController@verify');
		$router->post('generate_username', 'UserController@generate_username');
		$router->post('logout', 'UserController@logout');

		/**
		 * Purchase
		 */
		$router->post('pay', 'PurchaseController@pay');
		$router->post('paid', 'PurchaseController@paid');
		$router->post('cancel_pay', 'PurchaseController@cancel_pay');
	});

});
