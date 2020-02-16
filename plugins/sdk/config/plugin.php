<?php

return [
	'enabled' => true,
	'register' => [
		'view' => true,
		'migrate' => true,
		'translator' => true,
		'router' => true,
		'censor' => true,
		'event' => true,
	],
	'commands' => [

	],
	'configs' => [

	],
	'routeMiddleware' => [
		'encrypt-body-by-app-launch' => \Plugins\Sdk\App\Http\Middleware\EncryptBodyByAppLaunch::class
	],
	'injectViews' => [
	],
	'files' => [
	],
];
