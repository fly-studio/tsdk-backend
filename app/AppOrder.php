<?php

namespace App;

use App\Model;

class AppOrder extends Model {

	protected $guarded = ['id'];
	protected $casts = [
		'payment_type' => 'catalog',
		'app_order_status' => 'catalog',
		'order_params' => 'array',
	];

	public function app()
	{
		return $this->hasOne('App\\App', 'id', 'aid');
	}

	public function device()
	{
		// 目标表Model 中间表Model [中间M]和本M的关系字段 [目标M]和中间M的关系字段 [本M]和中间M的关系字段 [中间M]和目标M的关系字段  中括号表示是该M里面的字段
		return $this->hasOneThrough('App\\Device', 'App\\ProductDevice', 'id', 'id', 'adid', 'did');
	}

	public function app_device()
	{
		return $this->hasOne('App\\AppDevice', 'id', 'adid');
	}

	public function app_launch()
	{
		return $this->hasOne('App\\AppLaunch', 'id', 'alid');
	}

	public function app_user()
	{
		return $this->hasOne('App\\AppUser', 'id', 'auid');
	}

	public function user()
	{
		// 目标表Model 中间表Model [中间M]和本M的关系字段 [目标M]和中间M的关系字段 [本M]和中间M的关系字段 [中间M]和目标M的关系字段  中括号表示是该M里面的字段
		return $this->hasOneThrough('App\\User', 'App\\AppUser', 'id', 'id', 'auid', 'uid');
	}

}
