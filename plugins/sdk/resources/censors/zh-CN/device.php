<?php

return [
	'fields' => [
		'imei' => [
			'name' => '国际移动设备识别码',
			'rules' => 'nullable|min:9',
		],
		'idfa' => [
			'name' => '苹果IDFA',
			'rules' => 'nullable|min:9',
		],
		'oaid' => [
			'name' => '移动安全联盟的OAID',
			'rules' => 'nullable|min:9',
		],
		'android_id' => [
			'name' => 'Android ID',
			'rules' => [],
		],
		'serial' => [
			'name' => '设备序列号',
			'rules' => [],
		],
		'brand' => [
			'name' => '设备品牌',
			'rules' => [],
		],
		'model' => [
			'name' => '设备型号',
			'rules' => [],
		],
		'arch' => [
			'name' => 'CPU架构',
			'rules' => [],
		],
		'os' => [
			'name' => '操作系统',
			'rules' => [],
		],
		'os_version' => [
			'name' => '系统版本',
			'rules' => [],
		],
		'mac' => [
			'name' => 'Wifi MAC',
			'rules' => [],
		],
		'bluetooth' => [
			'name' => '蓝牙MAC',
			'rules' => [],
		],
		'metrics' => [
			'name' => '分辨率',
			'rules' => [],
		],
		'is_rooted' => [
			'name' => '是否越狱/Root',
			'rules' => 'bool',
		],
		'is_simulator' => [
			'name' => '是否模拟器',
			'rules' => 'bool',
		],
	],
];
