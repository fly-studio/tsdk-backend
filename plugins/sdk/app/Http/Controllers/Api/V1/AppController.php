<?php

namespace Plugins\Sdk\App\Http\Controllers\Api\V1;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Plugins\Sdk\App\Events\SdkEvent;
use App\Http\Controllers\Controller;
use Plugins\Sdk\App\Http\Controllers\LogTrait;
use Plugins\Sdk\App\Http\Controllers\CensorTrait;

use App\AppDevice;
use App\AppLaunch;
use App\Repositories\DeviceRepository;
use App\Repositories\AppLaunchRepository;

class AppController extends Controller
{
	use CensorTrait, LogTrait;

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
	 * @param  Request   $request
	 * @return Response
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
		(new SdkEvent($appLaunch, $property))
			->value(compact('device'))
			->from($appLaunch)
			->handle('launch');

		return $this->returnApi($appLaunch);
	}

	/**
	 * Activity onResume
	 * 此时理论上获得了IMEI值，调取本API来补充数据
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function start(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);

		$device = $this->censorDevice($request);
		$property = $this->censorProperty($request);

		$this->attachDevice($appLaunch->app_device, $device);

		(new SdkEvent($appLaunch, $property))
			->value(compact('device'))
			->handle('start');

		return $this->returnApi($appLaunch);
	}

	/**
	 * Activity onPause
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function pause(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		(new SdkEvent($appLaunch, $property))
			->handle('pause');

		return $this->success();
	}

	/**
	 * Activity onPause
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function tick(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		(new SdkEvent($appLaunch, $property))
			->handle('tick');

		return $this->success();
	}

	/**
	 * App Throw Exception
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function exception(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);

		$property = $this->censorProperty($request);

		$exception = $request->input('exception');

		(new SdkEvent($appLaunch, $property))
			->value(compact('exception'))
			->handle('exception');

		return $this->success();
	}

	/**
	 * App Crash
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function crash(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		$exception = $request->input('exception');

		(new SdkEvent($appLaunch, $property))
			->value(compact('exception'))
			->handle('crash');

		return $this->success();
	}

	/**
	 * launch/start 返回的response
	 * @param  AppLaunch $appLaunch
	 * @return Response
	 */
	private function returnApi(AppLaunch $appLaunch)
	{
		return $this->api([
			'needDeviceId' => empty($appLaunch->app_device->did),
			'alid' => $appLaunch->getKey(),
			'expired_at' => $appLaunch->expired_at->toW3cString(),
			'token' => $appLaunch->token
		]);
	}

	/**
	 * 关联设备
	 * 如果设备信息完整，则会将device关键到app_device
	 *
	 * @param  AppDevice $appDevice
	 * @param  array     $deviceInfo
	 * @return void
	 */
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
