<?php

return [
	'fields' => [
		'app_id' => [
			'name' => 'SDK App ID',
			'rules' => 'required|numeric',
		],
		'uuid' => [
			'name' => '设备唯一码',
			'rules' => 'required|uuid',
		],
		'device' => [
			'name' => '设备常量',
			'rules' => 'required|array',
		],
		'property' => [
			'name' => '设备属性',
			'rules' => 'required|array',
		],
	],
];
