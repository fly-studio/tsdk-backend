<?php

namespace App\Repositories;

use DB;
use Illuminate\Http\Request;
use Addons\Core\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;

use App\User;
use App\AppUser;
use App\AppLaunch;

class AppUserRepository extends Repository {

	public function prePage()
	{
		return config('size.models.'.(new AppUser)->getTable(), config('size.common'));
	}

	public function find($id, array $columns = ['*'])
	{
		return AppUser::with([])->find($id, $columns);
	}

	public function findOrFail($id, array $columns = ['*'])
	{
		return AppUser::with([])->findOrFail($id, $columns);
	}

	public function bindUser(AppLaunch $appLaunch, User $user)
	{
		return DB::transaction(function() use ($appLaunch, $user) {

			return AppUser::firstOrCreate([
				'aid' => $appLaunch->aid,
				'uid' => $user->getKey(),
			],[
				'alid' => $appLaunch->getKey(),
				'adid' => $appLaunch->adid,
			]);

		});
	}

	public function store(array $data)
	{
		return DB::transaction(function() use ($data) {
			$model = AppUser::create($data);
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
			AppUser::destroy($ids);
		});
	}

	public function data(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new AppUser;
		$builder = $model->newQuery()->with([]);

		$total = $this->_getCount($request, $builder, false);
		$data = $this->_getData($request, $builder, $callback, $columns);
		$data['recordsTotal'] = $total; //不带 f q 条件的总数
		$data['recordsFiltered'] = $data['total']; //带 f q 条件的总数

		return $data;
	}

	public function export(Request $request, callable $callback = null, array $columns = ['*'])
	{
		$model = new AppUser;
		$builder = $model->newQuery()->with([]);
		$size = $request->input('size') ?: config('size.export', 1000);

		$data = $this->_getExport($request, $builder, $callback, $columns);

		return $data;
	}

}
