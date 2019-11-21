<?php

namespace Plugins\Sdk\App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\DeviceRepository;

class SdkController extends Controller
{

	protected function updateDevice(array $inputs)
	{
		$deviceRepo = new DeviceRepository;

		return $deviceRepo->createOrUpdate($inputs['uuid'], Arr::only($inputs, [/*机器码*/'imei', 'udid', 'idfa', 'oaid', 'android_id', 'serial', /*机器配置*/'brand', 'model', 'arch', 'os', 'os_version', 'mac', 'bluetooth', 'metrics', 'is_root', 'is_simulator']));
	}

}
