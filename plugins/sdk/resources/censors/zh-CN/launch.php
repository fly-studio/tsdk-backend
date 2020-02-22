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
		'channel' => [
			'name' => '联运方',
			'rules' => 'required',
		],
		'sub_channel' => [
			'name' => '子渠道',
			'rules' => [],
		],
	],
];
