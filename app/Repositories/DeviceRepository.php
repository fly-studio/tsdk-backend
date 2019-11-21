<?php

namespace App\Repositories;

use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Addons\Core\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;

use App\Device;

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

	public function findByDeviceId(string $type, string $value)
	{
		if (!in_array($type, static::TRUSTED_DEVICE_ID) || empty($value))
			return null;

		return Device::where($type, $value)->first();
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

	public function updateDevice(string $uuid, array $inputs)
	{
		//$fields = [/*机器码*/'imei', 'udid', 'idfa', 'oaid', 'android_id', 'serial', /*机器配置*/'brand', 'model', 'arch', 'os', 'os_version', 'mac', 'bluetooth', 'metrics', 'is_root', 'is_simulator'];

		$device = null;

		foreach(static::TRUSTED_DEVICE_ID as $type)
		{
			if (!empty($inputs[$type]) && !empty($device = $this->findByDeviceId($type, $inputs[$type])))
				break;
		}

		// remove blank value
		$fields = array_filter($inputs, function($value) {
			return !empty($value);
		});

		return $this->updateOrCreate(!empty($device) ? $device['uuid'] : $uuid, $fields);
	}

	public function updateOrCreate(string $uuid, array $deviceInfo)
	{
		return DB::transaction(function() use ($uuid, $deviceInfo) {
			return Device::updateOrCreate(['uuid' => $uuid], $deviceInfo);
		});
	}
}
