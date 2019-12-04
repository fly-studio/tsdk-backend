<?php

namespace Plugins\Sdk\App\Http\Controllers\Api\V1;

use Event;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Plugins\Sdk\App\Events\SdkEvent;
use App\Http\Controllers\Controller;
use Plugins\Sdk\App\Http\Controllers\CensorTrait;

use App\AppDevice;
use App\AppLaunch;
use App\Repositories\DeviceRepository;
use App\Repositories\AppLaunchRepository;

class AppController extends Controller
{
	use CensorTrait;

	private $deviceRepo;

	public function __construct()
	{
		$this->deviceRepo = new DeviceRepository;
	}

	/**
	 * Application onCreate
	 *
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
		$data = $this->censor($request, 'sdk::launch.fields', ['app_id', 'uuid']);

		$device = $this->censorDevice($request);
		$property = $this->censorProperty($request);

		$app = $this->censorApp($request, $data['app_id']);

		//关联UUID+APPID
		$appDevice = $this->deviceRepo->bindUuid($app->getKey(), $data['uuid']);

		$this->attachDevice($appDevice, $device);

		// 添加启动记录
		$appLaunch = $appLaunchRepo->launch($app->getKey(), $appDevice->getKey());

		// 记录event
		(new SdkEvent($appLaunch, $property))->from($appLaunch)->handle('launch');

		return $this->returnApi($appLaunch);
	}

	/**
	 * Activity onResume
	 * 此时理论上获得了IMEI值，调取本API来补充数据
	 *
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function start(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);

		$device = $this->censorDevice($request);
		$property = $this->censorProperty($request);

		$this->attachDevice($appLaunch->app_device, $device);

		(new SdkEvent($appLaunch, $property))->from($appLaunch)->handle('start');

		return $this->returnApi($appLaunch);
	}

	/**
	 * Activity onPause
	 *
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function pause(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		(new SdkEvent($appLaunch, $property))->from($appLaunch)->handle('pause');

		return $this->returnApi($appLaunch);
	}

	/**
	 * Activity onPause
	 *
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function tick(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		(new SdkEvent($appLaunch, $property))->from($appLaunch)->handle('tick');

		return $this->returnApi($appLaunch);
	}

	/**
	 * App Throw Exception
	 *
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function exception(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);

		$property = $this->censorProperty($request);

		$exception = $request->input('exception');

		(new SdkEvent($appLaunch, $property))->from($appLaunch)->value($exception)->handle('exception');

		return $this->returnApi($appLaunch);
	}

	/**
	 * App Crash
	 *
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function crash(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		$exception = $request->input('exception');

		(new SdkEvent($appLaunch, $property))->from($appLaunch)->value($exception)->handle('crash');

		return $this->returnApi($appLaunch);
	}

	private function returnApi(AppLaunch $appLaunch)
	{
		return $this->api(['needDeviceId' => empty($appLaunch->app_device->did), 'alid' => $appLaunch->getKey(), 'expired_at' => $appLaunch->expired_at->toW3cString(), 'token' => $appLaunch->token]);
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
