<?php

namespace App;

use App\Model;
use Addons\Core\Models\TreeTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class App extends Model
{
	use TreeTrait, SoftDeletes;

	public $levelKey = null; //无需此字段请设置NULL   MySQL需加索引
	public $orderKey = null; //无需此字段请设置NULL   MySQL需加索引

	protected $casts = [
		'app_status' => 'catalog',
		'channel' => 'catalog',
		'sdk_params' => 'array',
	];

	public function launches()
	{
		return $this->hasMany('App\AppLaunch', 'aid', 'id');
	}
}
