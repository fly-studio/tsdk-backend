<?php

namespace Plugins\Sdk\App\Http\Controllers\Api\V1;

use Event;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Plugins\Sdk\App\Events\SdkEvent;
use App\Http\Controllers\Controller;
use Plugins\Sdk\App\Http\Controllers\CensorTrait;

use App\User;
use App\AppUser;
use App\AppLaunch;
use App\Repositories\UserRepository;
use App\Repositories\AppUserRepository;
use App\Repositories\AppLoginRepository;
use App\Repositories\AppLaunchRepository;

class UserController extends Controller
{
	use CensorTrait;

	protected $userRepo;
	protected $appUserRepo;

	public function __construct()
	{
		$this->userRepo = new UserRepository;
		$this->appUserRepo = new AppUserRepository;
	}

	public function register(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		$data = $this->censor($request, 'sdk::user.fileds', ['username', 'password']);
		$user = $this->userRepo->findByUsername($data['username']);

		if (!empty($user))
			return $this->error('sdk::user.username_registered')->code(4008);

		// 注册
		$user = $this->userRepo->store($data, 'user');

		// 捆绑到APP用户
		$appUser = $this->appUserRepo->bindUser($appLaunch, $user);

		(new SdkEvent($appLaunch, $property))
			->from($appUser)
			->appUser($appUser)
			->value($data + ['user' => $user->toArray(), 'appUser' => $appUser->toArray()])
			->handle('register');

		return $this->success('sdk::user.success_registered')->data(['uid' => $user->getKey(), 'auid' => $appUser->getKey(), 'username' => $user->username]);
	}

	public function login(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		$data = $this->censor($request, 'sdk::user.fileds', ['username', 'password']);
		$user = $this->userRepo->authenticate($data['username'], $data['password']);

		// 捆绑到APP用户 or 读取
		$appUser = $this->appUserRepo->bindUser($appLaunch, $user);

		if (empty($user))
			return $this->error('sdk::user.error_login')->code(4009);

		// 添加登录记录，此表是计算新增、留存的关键表
		$appLogin = (new AppLoginRepository)->login($appLaunch, $appUser);

		(new SdkEvent($appLaunch, $property))
			->from($appLogin)
			->appUser($appUser)
			->value($data + ['uid' => $user->getKey(), 'auid' => $appUser->getKey()])
			->handle('login');

		$at = time();

		return $this->success('sdk::user.success_login')->data([
			'uid' => $user->getKey(),
			'auid' => $appUser->getKey(),
			'username' => $user->username,
			'at' => $at,
			'sign' => $this->getSign($user, $appUser, $at)
		]);
	}

	public function verify(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		[$user, $appUser] = $this->censorUser($request);

		$data = $this->censor($request, 'sdk::user.verify', ['at', 'sign']);

		if ($data['at'] < time() - 300)
			return $this->error('sdk::user.sign_timeout')->code(4010);

		if (strcasecmp($data['sign'], $this->getSign($user, $appUser, $data['at'])) != 0)
			return $this->error('sdk::user.sign_invalid')->code(4011);

		(new SdkEvent($appLaunch, $property))
			->appUser($appUser)
			->value($data + ['uid' => $user->getKey(), 'auid' => $appUser->getKey()])
			->handle('verify');

		return $this->success('sdk::user.sign_correct');
	}

	public function generate_username(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		$username = '';

		do {
			$username = 'ts'.mt_rand(10000000, 99999999);
		} while (!empty($this->userRepo->findByUsername($username)));

		(new SdkEvent($appLaunch, $property))
			->value(['username' => $username])
			->handle('generate_username');

		return $this->api(['username' => $username]);
	}

	public function logout(Request $request)
	{
		$appLaunch = $this->censorAppLaunch($request);
		$property = $this->censorProperty($request);

		[$user, $appUser] = $this->censorUser($request);

		(new SdkEvent($appLaunch, $property))
			->appUser($appUser)
			->value(['uid' => $user->getKey(), 'auid' => $appUser->getKey()])
			->handle('logout');

		return $this->success();
	}

	private function getSign(User $user, AppUser $appUser, int $at)
	{
		$salt = $user->password;
		return md5($user->getKey().$appUser->getKey().$user->username.$at.$salt);
	}
}
