<?php

namespace App;

use App\Model;

class Device extends Model
{
	protected $dates = ['last_at'];
	protected $appends = ['brand_model'];

	public function getBrandModelAttribute()
	{
		return $this->brand.' '.$this->model;
	}

	public function app_device()
	{
		return $this->hasMany('App\\AppDevice', 'did', 'id');
	}

	public function logins()
	{
		// 目标表Model 中间表Model [中间M]和本M的关系字段 [目标M]和中间M的关系字段 [本M]和中间M的关系字段 [中间M]和目标M的关系字段  中括号表示是该M里面的字段
		return $this->hasManyThrough('App\\AppLogin', 'App\\AppDevice', 'did', 'adid', 'id', 'id');
	}

	public function launches()
	{
		// 目标表Model 中间表Model [中间M]和本M的关系字段 [目标M]和中间M的关系字段 [本M]和中间M的关系字段 [中间M]和目标M的关系字段  中括号表示是该M里面的字段
		return $this->hasManyThrough('App\\AppLaunch', 'App\\AppDevice', 'did', 'adid', 'id', 'id');
	}

	public function orders()
	{
		// 目标表Model 中间表Model [中间M]和本M的关系字段 [目标M]和中间M的关系字段 [本M]和中间M的关系字段 [中间M]和目标M的关系字段  中括号表示是该M里面的字段
		return $this->hasManyThrough('App\\AppOrder', 'App\\AppDevice', 'did', 'adid', 'id', 'id');
	}

	public function users()
	{
		// 目标表Model 中间表Model [中间M]和本M的关系字段 [目标M]和中间M的关系字段 [本M]和中间M的关系字段 [中间M]和目标M的关系字段  中括号表示是该M里面的字段
		return $this->hasManyThrough('App\\AppUser', 'App\\AppDevice', 'did', 'adid', 'id', 'id');
	}
}
