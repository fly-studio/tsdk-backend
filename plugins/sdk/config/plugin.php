<?php

return [
	'enabled' => true,
	'register' => [
		'view' => true,
		'migrate' => true,
		'translator' => false,
		'router' => true,
		'censor' => true,
	],
	'commands' => [

	],
	'configs' => [

	],
	'routeMiddleware' => [
		'check-sign' => \Plugins\Sdk\App\Http\Middleware\CheckSign::class,
	],
	'injectViews' => [
	],
	'files' => [
	],
];
