<?php

return [
	'fields' => [
		'game_id' => [
			'name' => '游戏ID',
			'rules' => 'required',
		],
		'app_id' => [
			'name' => 'App ID',
			'rules' => 'required',
		],
		'imei' => [
			'name' => 'IMEI',
			'rules' => [],
		],
		'udid' => [
			'name' => 'UDID',
			'rules' => [],
		],
		'device' => [
			'name' => '设备类型',
			'rules' => [],
		],
		'platform' => [
			'name' => '手机平台',
			'rules' => [],
		],
		'os' => [
			'name' => '操作系统',
			'rules' => [],
		],
		'os_version' => [
			'name' => '操作系统版本',
			'rules' => [],
		]
	],
];
