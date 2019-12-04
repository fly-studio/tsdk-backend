<?php

return [
	'fields' => [
		'item_name' => [
			'name' => '商品名',
			'rules' => 'required',
		],
		'cp_order_no' => [
			'name' => '游戏订单号',
			'rules' => 'required',
		],
		'amount' => [
			'name' => '金额',
			'rules' => 'required|numeric|min:0',
		],
		'order_params' => [
			'name' => '透传',
			'rules' => 'required|array',
		],
		'oid' => [
			'name' => '订单ID',
			'rules' => 'required|numeric',
		],

	],
];
