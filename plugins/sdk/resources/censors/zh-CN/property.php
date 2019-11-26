<?php

return [
	'fields' => [
		'carrier' => [
			'name' => '运营商',
			'rules' => [],
		],
		'connection' => [
			'name' => '网络连接',
			'rules' => [],
		],
		'app_version_code' => [
			'name' => 'APP版本代码',
			'rules' => 'required',
		],
		'app_version' => [
			'name' => 'APP版本',
			'rules' => 'required',
		],
		'sdk_version' => [
			'name' => 'SDK版本',
			'rules' => 'required',
		],
		'geometry' => [
			'name' => '地理坐标',
			'rules' => [],
		],
		'device_at' => [
			'name' => '设备W3C时间',
			'rules' => ['required', 'date_format:'.\DateTime::W3C],
		],
	],
];
