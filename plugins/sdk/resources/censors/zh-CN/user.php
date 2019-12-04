<?php

return [
	'fields' => [
		'username' => [
			'name' => '用户名',
			'rules' => 'required|min:6|max:150',
		],
		'password' => [
			'name' => '密码',
			'rules' => 'required|min:6|max:32',
		],
		'uid' => [
			'name' => 'User ID',
			'rules' => 'required|numeric',
		],
		'auid' => [
			'name' => 'App User ID',
			'rules' => 'required|numeric',
		],
	],
	'verify' => [
		'at' => [
			'name' => '时间戳',
			'rules' => 'required|numeric',
		],
		'sign' => [
			'name' => '签名',
			'rules' => 'required|string|size:32',
		],
	],
];
