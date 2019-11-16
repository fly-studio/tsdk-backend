<?php

namespace App;

use App\Model;

class Device extends Model
{
	protected $dates = ['last_at'];
}

Device::creating(function($model) {
	$model->device_binary = str2bin($model->device_id);
});
