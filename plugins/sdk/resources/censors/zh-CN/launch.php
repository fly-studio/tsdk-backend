<?php

return [
	'fields' => [
		'app_id' => [
			'name' => 'SDK App ID',
			'rules' => 'required|numeric',
		],
		'uuid' => [
			'name' => '设备唯一码',
			'rules' => 'required|string|size:32',
		],
		'device' => [
			'name' => '设备常量',
			'rules' => 'required|array',
		],

	],
];
