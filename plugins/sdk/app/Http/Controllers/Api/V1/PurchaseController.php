<?php

namespace Plugins\Sdk\App\Http\Controllers\Api\V1;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Plugins\Sdk\App\Events\SdkEvent;
use App\Http\Controllers\Controller;
use Plugins\Sdk\App\Http\Controllers\LogTrait;
use Plugins\Sdk\App\Http\Controllers\CensorTrait;

use App\AppUser;
use App\AppOrder;
use App\AppLaunch;
use App\Repositories\AppOrderRepository;

class PurchaseController extends Controller
{
	use CensorTrait, LogTrait;

	protected $appOrderRepo;

	public function __construct()
	{
		$this->appOrderRepo = new AppOrderRepository;
	}

	/**
	 * 唤起支付，返回订单
	 * @param  Request $request
	 * @return Response
	 */
	public function pay(Request $request, AppLaunch $appLaunch)
	{
		$property = $this->censorProperty($request);
		[$user, $appUser] = $this->censorUser($request);

		$data = $this->censor($request, 'sdk::purchase.fields', ['item_name', 'cp_order_no', 'amount', 'order_params']);

		// 获取本地订单信息
		$order = $this->appOrderRepo->order($appLaunch, $appUser, $data);

		(new SdkEvent($appLaunch, $property))
			->from($order)
			->appUser($appUser)
			->value($data)
			->handle('pay');

		return $this->api([
			'oid' => $order->getKey(),
			'uid' => $user->getKey(),
			'auid' => $appUser->getKey(),
			'order_no' => $order->order_no,
			'item_name' => $order->item_name,
			'amount' => $order->amount,
			'cp_order_no' => $order->cp_order_no,
			'order_params' => $order->order_params,
		]);
	}

	/**
	 * 前端通知支付成功
	 * 比如ysdk的网游模式会以前端的方式通知支付成功, 此时需要去ysdk对账才能发货
	 * 其它都是后台对账，所以此函数只用于event
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function paid(Request $request, AppLaunch $appLaunch)
	{
		$property = $this->censorProperty($request);
		[$user, $appUser] = $this->censorUser($request);

		$order = $this->censorOrder($request);

		(new SdkEvent($appLaunch, $property))
			->from($order)
			->appUser($appUser)
			->handle('paid');

		return $this->api();
	}

	/**
	 * 前端取消支付
	 * 仅做event用
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function cancel_pay(Request $request, AppLaunch $appLaunch)
	{
		$property = $this->censorProperty($request);
		[$user, $appUser] = $this->censorUser($request);

		$order = $this->censorOrder($request);

		(new SdkEvent($appLaunch, $property))
			->from($order)
			->appUser($appUser)
			->handle('cancel_pay');

		return $this->success();
	}

	/**
	 * 应用市场支付成功之后，Callback我们的网址
	 *
	 * @param  Request  $request [description]
	 * @return Response
	 */
	public function callback(Request $request, AppLaunch $appLaunch, string $channel)
	{
		$channel = catalog_search('fields.channel.'.$channel);

		// Invalid channel
		if (empty($channel))
		{
			// Todo
			$this->log()->error('[Purchase]Invalid channel: '.$channel);

			return ;
		}

	}
}
