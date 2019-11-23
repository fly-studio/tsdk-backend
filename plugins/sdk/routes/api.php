<?php

$router->api('v1', function($router) {

	$router->group(['prefix' => 'sdk'], function($router){
		$router->get('launch', 'LaunchController@index');
		$router->post('supplement', 'LaunchController@supplement');
	});

});
