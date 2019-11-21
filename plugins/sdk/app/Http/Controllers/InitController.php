<?php

namespace Plugins\Sdk\App\Http\Controllers;

use Event;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Addons\Core\Exceptions\OutputResponseException;

use App\Repositories\DeviceRepository;
use App\Repositories\AppLaunchRepository;

class InitController extends Controller
{
	/**
	 * 初始化逻辑
	 * 1. SDK在本地文件读取tsdk/device文件，没有就生成uuid
	 * 2. 将本地能够读取到的设备信息都补充到这个文件，也就是说，SDK旗下的其它APP也可以可以相互补充
	 * Android 10设备唯一值获取：https://developer.android.com/training/articles/user-data-ids
	 *
	 * @param  Request             $request
	 * @param  DeviceRepository    $deviceRepo
	 * @param  AppLaunchRepository $appLaunchRepo
	 * @return [type]
	 */
	public function index(Request $request, DeviceRepository $deviceRepo, AppLaunchRepository $appLaunchRepo,)
	{
		$data = $this->censor($request, 'sdk::init.fields', ['app_id', 'uuid', 'device', 'property']);

		// 查找或者新建设备记录
		$device = $deviceRepo->updateDevice($data['uuid'], $data['device']);

		// 添加启动记录
		$appLaunch = $appLaunchRepo->launch($data['app_id'], $device->getKey());

		// 记录event
		(new SdkEvent($appLaunch, $data['property']))->from($appLaunch)->handle('launch');

		return $this->api(['alid' => $appLaunch->getKey(), 'expired_at' => $appLaunch->expired_at->toW3cString(), 'token' => $appLaunch->token, 'device' => $device]);
	}

}
