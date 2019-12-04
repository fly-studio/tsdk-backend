<?php

$eventer->group(['namespace' => 'Controllers\\Api'], function($eventer) {
	$eventer->controller('*', '\Plugins\Sdk\App\Listeners\LogRequest', 'before');
});
