<?php

namespace Plugins\Sdk\App\Http\Controllers;

use Event;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Plugins\Sdk\App\Http\Controllers\CommonTrait;
use Addons\Core\Exceptions\OutputResponseException;

use App\AppDevice;
use App\Repositories\DeviceRepository;

class LaunchController extends Controller
{
	use CommonTrait;

	private $deviceRepo;

	public function __construct()
	{
		$this->deviceRepo = new DeviceRepository;
	}
	/**
	 * 初始化逻辑
	 * 1. 第一次Application onCreate，无任何权限，APP生成一个uuid并存入APP中，然后远程请求本API
	 * 2. 第二次Application onCreate，APP读取uuid，
	 *
	 *
	 * Android 10设备唯一值获取：https://developer.android.com/training/articles/user-data-ids
	 *
	 * @param  Request             $request
	 * @return [type]
	 */
	public function index(Request $request, AppLaunchRepository $appLaunchRepo)
	{
		$data = $this->censor($request, 'sdk::launch.fields', ['app_id', 'uuid', 'device']);

		$property = $this->censorProperty($request);

		$app = $this->censorApp($data['app_id']);

		//关联UUID+APPID
		$appDevice = $this->deviceRepo->attachUuid($app->getKey(), $data['uuid']);

		$this->attachDevice($appDevice, $data['device']);

		// 添加启动记录
		$appLaunch = $appLaunchRepo->launch($app->getKey(), $appDevice->getKey());

		// 记录event
		(new SdkEvent($appLaunch, $property))->from($appLaunch)->handle('launch');

		return $this->api(['needDeviceId' => empty($appDevice->did), 'alid' => $appLaunch->getKey(), 'expired_at' => $appLaunch->expired_at->toW3cString(), 'token' => $appLaunch->token]);
	}

	/**
	 * 获得了IMEI值，调取本API来补充数据
	 *
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function supplement(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);

		$property = $this->censorProperty($request);

		$data = $this->censor($request, 'sdk::launch.fields', ['device']);

		$this->attachDevice($appLaunch->app_device, $data['device']);

		(new SdkEvent($appLaunch, $property))->from($appLaunch)->handle('supplement');

		return $this->success();
	}

	private function attachDevice(AppDevice $appDevice, array $deviceInfo)
	{
		// 更新设备信息，如果不存在，则新建
		$device = $this->deviceRepo->updateDevice($deviceInfo);

		// 不相等，就修改did
		if (!empty($device) && $appDevice->did != $device->getKey())
		{
			$appDevice->did = $device->getKey();
			$appDevice->save();
		}
	}

}
