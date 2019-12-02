<?php

namespace App;

use App\Model;
use Addons\Core\Models\TreeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class App extends Model
{
	use TreeTrait, SoftDeletes;

	protected $guarded = ['id'];
	protected $casts = [
		'app_status' => 'catalog',
		'channel' => 'catalog',
		'sdk_params' => 'array',
	];

	public function app_devices()
	{
		return $this->hasMany('App\\AppDevice', 'aid', 'id');
	}

	public function app_launches()
	{
		return $this->hasMany('App\\AppLaunch', 'aid', 'id');
	}

	public function app_users()
	{
		return $this->hasMany('App\\AppUser', 'aid', 'id');
	}

	public function app_logins()
	{
		return $this->hasMany('App\\AppLogin', 'aid', 'id');
	}

	public function app_orders()
	{
		return $this->hasMany('App\\AppOrder', 'aid', 'id');
	}

	public function app_events()
	{
		return $this->hasMany('App\\AppEvent', 'aid', 'id');
	}

	public function app_stats()
	{
		return $this->hasMany('App\\AppStat', 'aid', 'id');
	}

	public function app_daily_stats()
	{
		return $this->hasMany('App\\AppDailyStat', 'aid', 'id');
	}

	public function getLevelKeyName()
	{
		return null;
	}

	public function getOrderKeyName()
	{
		return null;
	}

}
