<?php

namespace Plugins\Sdk\App\Http\Controllers\Api\V1;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Plugins\Sdk\App\Events\SdkEvent;
use App\Http\Controllers\Controller;
use Plugins\Sdk\App\Http\Controllers\LogTrait;
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
	use CensorTrait, LogTrait;

	protected $userRepo;
	protected $appUserRepo;

	public function __construct()
	{
		$this->userRepo = new UserRepository;
		$this->appUserRepo = new AppUserRepository;
	}

	/**
	 * 提交账密来注册
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function register(Request $request, $appLaunch)
	{
		$property = $this->censorProperty($request);

		$data = $this->censor($request, 'sdk::user.fileds', ['username', 'password']);
		$user = $this->userRepo->findByUsername($data['username']);

		if (!empty($user))
			return $this->error('sdk::user.username_registered')->code(4013);

		// 注册
		$user = $this->userRepo->store($data, 'user');

		// 捆绑到APP用户
		$appUser = $this->appUserRepo->bindUser($appLaunch, $user);

		(new SdkEvent($appLaunch, $property))
			->from($appUser)
			->appUser($appUser)
			->value($data)
			->handle('register');

		return $this->success('sdk::user.success_registered')->data(['uid' => $user->getKey(), 'auid' => $appUser->getKey(), 'username' => $user->username]);
	}

	/**
	 * 提交账密来登录
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function login(Request $request, $appLaunch)
	{
		$property = $this->censorProperty($request);

		$data = $this->censor($request, 'sdk::user.fileds', ['username', 'password']);
		$user = $this->userRepo->authenticate($data['username'], $data['password']);

		// 捆绑到APP用户 or 读取
		$appUser = $this->appUserRepo->bindUser($appLaunch, $user);

		if (empty($user))
			return $this->error('sdk::user.error_login')->code(4014);

		// 添加登录记录，此表是计算新增、留存的关键表
		$appLogin = (new AppLoginRepository)->login($appLaunch, $appUser);

		(new SdkEvent($appLaunch, $property))
			->from($appLogin)
			->appUser($appUser)
			->value($data)
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

	/**
	 * 登录验签
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function verify(Request $request, $appLaunch)
	{
		$property = $this->censorProperty($request);

		[$user, $appUser] = $this->censorUser($request);

		$data = $this->censor($request, 'sdk::user.verify', ['at', 'sign']);

		// Sign是否过期
		if ($data['at'] < time() - 300)
			return $this->error('sdk::user.sign_timeout')->code(4015);

		// 检查Sign
		if (strcasecmp($data['sign'], $this->getSign($user, $appUser, $data['at'])) != 0)
			return $this->error('sdk::user.sign_invalid')->code(4016);

		(new SdkEvent($appLaunch, $property))
			->appUser($appUser)
			->value($data)
			->handle('verify');

		return $this->success('sdk::user.sign_correct');
	}

	/**
	 * 快速注册获取用户名
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function generate_username(Request $request, $appLaunch)
	{
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

	/**
	 * 登出
	 * 仅用于event
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function logout(Request $request, $appLaunch)
	{
		$property = $this->censorProperty($request);

		[$user, $appUser] = $this->censorUser($request);

		(new SdkEvent($appLaunch, $property))
			->appUser($appUser)
			->handle('logout');

		return $this->success();
	}

	/**
	 * 登录验签算法
	 *
	 * @param  User    $user
	 * @param  AppUser $appUser
	 * @param  int     $at
	 * @return string
	 */
	private function getSign(User $user, AppUser $appUser, int $at)
	{
		$salt = $user->password;
		return md5($user->getKey().$appUser->getKey().$user->username.$at.$salt);
	}
}
