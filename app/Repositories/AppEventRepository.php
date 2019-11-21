<?php

namespace App\Repositories;

use DB;
use Illuminate\Http\Request;
use Addons\Core\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;

use App\Catalog;
use App\AppUser;
use App\AppEvent;
use App\AppLaunch;

class AppEventRepository extends Repository {

	public function prePage()
	{
		return config('size.models.'.(new AppEvent)->getTable(), config('size.common'));
	}

	public function find($id, array $columns = ['*'])
	{
		return AppEvent::with([])->find($id, $columns);
	}

	public function findOrFail($id, array $columns = ['*'])
	{
		return AppEvent::with([])->findOrFail($id, $columns);
	}

	public function handle(Catalog $event_type, AppLaunch $appLaunch, array $property, ?array $value, ?AppUser $appUser, ?Model $from)
	{
		$event = $this->store([
				'event_type' => $event_type,
				'aid' => $appLaunch->aid,
				'did' => $appLaunch->did,
				'auid' => !empty($appUser) $appUser->getKey() : null,
				'value' => $value,
				'ip' => app('request')->ip()
			] + $property
		);

		if (!empty($from))
			$event->table()->attach($from);

		return $event;
	}

	public function store(array $data)
	{
		return DB::transaction(function() use ($data) {
			$model = AppEvent::create($data);
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
			AppEvent::destroy($ids);
		});
	}

	public function data(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new AppEvent;
		$builder = $model->newQuery()->with([]);

		$total = $this->_getCount($request, $builder, false);
		$data = $this->_getData($request, $builder, $callback, $columns);
		$data['recordsTotal'] = $total; //不带 f q 条件的总数
		$data['recordsFiltered'] = $data['total']; //带 f q 条件的总数

		return $data;
	}

	public function export(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new AppEvent;
		$builder = $model->newQuery()->with([]);
		$size = $request->input('size') ?: config('size.export', 1000);

		$data = $this->_getExport($request, $builder, $callback, $columns);

		return $data;
	}

}
