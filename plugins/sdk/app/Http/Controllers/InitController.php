<?php

namespace Plugins\Sdk\App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Addons\Core\Exceptions\OutputResponseException;

use App\Repositories\DeviceRepository;

class InitController extends Controller
{
	/**
	 * 初始化逻辑
	 * 1. SDK在本地文件读取tsdk/device文件，没有就生成uuid
	 * 2. 将本地能够读取到的设备信息都补充到这个文件，也就是说，SDK旗下的其它APP也可以可以相互补充
	 * Android 10设备唯一值获取：https://developer.android.com/training/articles/user-data-ids
	 *
	 * @param  Request             $request       [description]
	 * @param  DeviceRepository    $deviceRepo    [description]
	 * @param  AppLaunchRepository $appLaunchRepo [description]
	 * @return [type]                             [description]
	 */
	public function index(Request $request, DeviceRepository $deviceRepo, AppLaunchRepository $appLaunchRepo)
	{
		$json = $request->json();

		$data = $this->censor($request, 'sdk::device.fields', ['app_id', 'uuid']);

		$device = $deviceRepo->createOrUpdate($data['uuid'], Arr::only($json, [/*机器码*/'imei', 'udid', 'idfa', 'oaid', 'android_id', 'serial', /*机器配置*/'brand', 'model', 'arch', 'os', 'os_version', 'mac', 'bluetooth', 'metrics', 'is_root', 'is_simulator']));

		$token = $appLaunchRepo->create($data['app_id'], $device->getKey());

		return $this->api(['token' => $token, 'uuid' => $data['uuid']]);
	}

}
