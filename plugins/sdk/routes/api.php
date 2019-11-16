<?php

$router->api('v1', function($router) {

	$router->group(['prefix' => 'sdk'], function($router){
		$router->get('init', 'InitController@index');

		$router->group(['middleware' => 'sign'], function($router) {
			$router->post('test', function(){
				return 'ok';
			});
		});

	});

});
