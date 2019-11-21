<?php

return [
	'fields' => [
		'app_id' => [
			'name' => 'SDK App ID',
			'rules' => 'required|numeric',
		],
		'uuid' => [
			'name' => '唯一码',
			'rules' => 'required|uuid',
		],
		'sdk_version' => [
			'name' => 'SDK版本号',
			'rules' => 'required',
		],
	],
];
