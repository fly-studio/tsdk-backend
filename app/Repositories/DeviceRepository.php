<?php

namespace App\Repositories;

use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Addons\Core\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;

use App\Device;
use App\AppDevice;

class DeviceRepository extends Repository {

	const TRUSTED_DEVICE_ID = ['imei', 'oaid', 'idfa'];

	public function prePage()
	{
		return config('size.models.'.(new Device)->getTable(), config('size.common'));
	}

	public function find($id, array $columns = ['*'])
	{
		return Device::with([])->find($id, $columns);
	}

	public function findByDeviceId(array $deviceInfo)
	{
		foreach(static::TRUSTED_DEVICE_ID as $type)
		{
			if (!empty($deviceInfo[$type]) &&
				!empty($device = Device::where($type, $deviceInfo[$type])->where('model', $deviceInfo['model'])->where('brand', $deviceInfo['brand'])->first())
			)
				return $device;
		}

		return null;
	}

	public function findOrFail($id, array $columns = ['*'])
	{
		return Device::with([])->findOrFail($id, $columns);
	}

	public function store(array $data)
	{
		return DB::transaction(function() use ($data) {
			$model = Device::create($data);
			return $model;
		});
	}

	public function update(Model $model, array $data)
	{
		return DB::transaction(function() use ($model, $data){
			$model->update($data);
			return $model;
		});
	}

	public function destroy(array $ids)
	{
		DB::transaction(function() use ($ids) {
			Device::destroy($ids);
		});
	}

	public function data(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new Device;
		$builder = $model->newQuery()->with([]);

		$total = $this->_getCount($request, $builder, false);
		$data = $this->_getData($request, $builder, $callback, $columns);
		$data['recordsTotal'] = $total; //不带 f q 条件的总数
		$data['recordsFiltered'] = $data['total']; //带 f q 条件的总数

		return $data;
	}

	public function export(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new Device;
		$builder = $model->newQuery()->with([]);
		$size = $request->input('size') ?: config('size.export', 1000);

		$data = $this->_getExport($request, $builder, $callback, $columns);

		return $data;
	}

	public function isEmptyTrustedDeviceId(array $deviceInfo)
	{
		foreach (static::TRUSTED_DEVICE_ID as $type) {
			if (!empty($deviceInfo[$type]))
				return false;
		}

		return true;
	}

	/**
	 * 将uuid和appid进行关联
	 * 新建或者读取
	 *
	 * @param  string $app_id
	 * @param  string $uuid
	 * @return [type]
	 */
	public function bindUuid(string $aid, string $uuid)
	{
		$uuid = str_replace('-', '', $uuid);

		return AppDevice::firstOrCreate(compact('aid', 'uuid'));
	}

	/**
	 * 存储设备信息
	 * 仅仅当TRUSTED_DEVICE_ID存在任一项时，方会新建或更新
	 *
	 * @param  array  $deviceInfo 设备的常量
	 * @return [type]
	 */
	public function updateDevice(array $deviceInfo)
	{
		//$fields = [/*机器码*/'imei', 'idfa', 'oaid', 'android_id', 'serial', /*机器配置*/'brand', 'model', 'arch', 'os', 'os_version', 'mac', 'bluetooth', 'metrics', 'is_root', 'is_simulator'];

		if ($this->isEmptyTrustedDeviceId($deviceInfo))
			return null;

		$device = $this->findByDeviceId($deviceInfo);

		// remove blank value
		$data = array_filter($deviceInfo, function($value) {
			return !empty($value);
		});

		if (empty($data))
			return $device;

		return !empty($device) ? $this->update($device, $data) : $this->store($data);
	}

}
