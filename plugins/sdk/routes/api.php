<?php

$router->api('v1', function($router) {

	$router->group(['prefix' => 'sdk', 'middleware' => []], function($router){

		$router->get('ping', function(){
			return 'pong';
		});

		/**
		 * APP
		 */
		$router->post('launch', 'AppController@launch')->middleware(['encrypt-body']);

		$router->group(['middleware' => ['encrypt-body-by-app-launch']], function($router) {


			/**
			 * APP
			 */
			$router->post('start/{appLaunch}', 'AppController@start');
			$router->post('pause/{appLaunch}', 'AppController@pause');
			$router->post('exception/{appLaunch}', 'AppController@exception');
			$router->post('crash/{appLaunch}', 'AppController@crash');
			$router->post('tick/{appLaunch}', 'AppController@tick');

			/**
			 * User
			 */
			$router->post('register/{appLaunch}', 'UserController@register');
			$router->post('login/{appLaunch}', 'UserController@login');
			$router->post('verify/{appLaunch}', 'UserController@verify');
			$router->post('generate_username/{appLaunch}', 'UserController@generate_username');
			$router->post('logout/{appLaunch}', 'UserController@logout');

			/**
			 * Purchase
			 */
			$router->post('pay/{appLaunch}', 'PurchaseController@pay');
			$router->post('paid/{appLaunch}', 'PurchaseController@paid');
			$router->post('cancel_pay/{appLaunch}', 'PurchaseController@cancel_pay');
			$router->post('callback//{appLaunch}/{channel}', 'PurchaseController@callback');
		});

	});

});
