<?php

namespace App;

use App\Model;

class AppLaunch extends Model
{
	protected $dates = ['expired_at'];

	public function device()
	{
		// 最终表，中间表，中间表外键->最终表键，本表键->中间表键
		// 本表键 -> 中间表相关键 -> 中间表第二键 -> 最终表相关键
		return $this->hasOneThrough('App\\Device', 'App\\AppDevice', 'did', 'id', 'adid', 'id');
	}

	public function app_device()
	{
		return $this->hasOne('App\\AppDevice', 'id', 'adid');
	}

	public function app()
	{
		return $this->hasOne('App\\App', 'id', 'aid');
	}

}
