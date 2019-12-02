<?php

namespace App;

use App\Model;

class AppDevice extends Model
{
	protected $guarded = ['id'];

	public function app()
	{
		return $this->hasOne('App\\App', 'id', 'aid');
	}

	public function device()
	{
		return $this->hasOne('App\\Device', 'id', 'did');
	}

	public function app_logins()
	{
		return $this->hasMany('App\\AppLogin', 'adid', 'id');
	}

	public function app_launches()
	{
		return $this->hasMany('App\\AppLaunch', 'adid', 'id');
	}

	public function app_orders()
	{
		return $this->hasMany('App\\AppOrder', 'adid', 'id');
	}

	public function app_users()
	{
		return $this->hasMany('App\\AppUser', 'adid', 'id');
	}

	public function users()
	{
		// 目标表Model 中间表Model [中间M]和本M的关系字段 [目标M]和中间M的关系字段 [本M]和中间M的关系字段 [中间M]和目标M的关系字段  中括号表示是该M里面的字段
		return $this->hasManyThrough('App\\User', 'App\\AppUser', 'id', 'id', 'auid', 'uid');
	}

}

AppDevice::creating(function($model) {
	$model->uuid_binary = hex2bin($model->uuid);
});
