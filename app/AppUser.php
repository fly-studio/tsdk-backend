<?php

namespace App;

use App\Model;

class AppUser extends Model
{
	protected $guarded = ['id'];

	public function app()
	{
		return $this->hasOne('App\\App', 'id', 'aid');
	}

	public function user()
	{
		return $this->hasOne('App\\User', 'id', 'uid');
	}

	public function app_launches()
	{
		return $this->hasMany('App\\AppLaunch', 'adid', 'id');
	}

	public function app_device()
	{
		return $this->hasOne('App\\AppDevice', 'id', 'adid');
	}

	public function device()
	{
		// 目标表Model 中间表Model [中间M]和本M的关系字段 [目标M]和中间M的关系字段 [本M]和中间M的关系字段 [中间M]和目标M的关系字段  中括号表示是该M里面的字段
		return $this->hasOneThrough('App\\Device', 'App\\AppDevice', 'id', 'id', 'adid', 'did');
	}

	public function app_logins()
	{
		return $this->hasMany('App\\AppLogin', 'auid', 'id');
	}

	public function app_orders()
	{
		return $this->hasMany('App\\AppOrder', 'auid', 'id');
	}

	public function app_events()
	{
		return $this->hasMany('App\\AppEvent', 'auid', 'id');
	}

}

AppDevice::creating(function($model) {
	$model->cp_user_binary = md5($model->cp_user_id, true);
});
