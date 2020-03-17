<?php

namespace App;

use App\Model;

class AppLaunch extends Model
{
	protected $guarded = ['id'];
	protected $dates = ['expired_at'];
	protected $hidden = ['public_key', 'private_key'];

	public function app()
	{
		return $this->hasOne('App\\App', 'id', 'aid');
	}

	public function device()
	{
		// 目标表Model 中间表Model [中间M]和本M的关系字段 [目标M]和中间M的关系字段 [本M]和中间M的关系字段 [中间M]和目标M的关系字段  中括号表示是该M里面的字段
		return $this->hasOneThrough('App\\Device', 'App\\AppDevice', 'id', 'id', 'adid', 'did');
	}

	public function app_device()
	{
		return $this->hasOne('App\\AppDevice', 'id', 'adid');
	}

	public function logins()
	{
		return $this->hasMany('App\\AppLogin', 'alid', 'id');
	}

	public function orders()
	{
		return $this->hasMany('App\\AppOrder', 'alid', 'id');
	}

	public function app_users()
	{
		return $this->hasMany('App\\AppUser', 'alid', 'id');
	}

}
