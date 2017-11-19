<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Str;
use Doctrine\DBAL\Driver\PDOException;
use Illuminate\Database\QueryException;
use Addons\Core\Http\OutputResponseFactory;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Addons\Entrust\Exception\PermissionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that are not reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		\Illuminate\Auth\AuthenticationException::class,
		\Illuminate\Auth\Access\AuthorizationException::class,
		\Symfony\Component\HttpKernel\Exception\HttpException::class,
		\Illuminate\Database\Eloquent\ModelNotFoundException::class,
		\Illuminate\Session\TokenMismatchException::class,
		\Illuminate\Validation\ValidationException::class,
		\Addons\Entrust\Exception\PermissionException::class,
	];
	/**
	 * A list of the inputs that are never flashed for validation exceptions.
	 *
	 * @var array
	 */
	protected $dontFlash = [
		'password',
		'password_confirmation',
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $exception
	 * @return void
	 */
	public function report(Exception $exception)
	{
		parent::report($exception);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $exception
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $exception)
	{
		if (!config('app.debug', false))
		{
			// 当findOrFail等情况下出现的报错
			if($exception instanceof ModelNotFoundException)
			{
				$traces = $exception->getTrace();
				$replaced_paths = array_merge([base_path()], array_map(function($v) {return realpath($v);}, config('plugins.paths')));
				foreach($traces as $key => $value)
				{
					if ($value['function'] == '__callStatic' && Str::endsWith($value['args'][0], 'OrFail'))
					{
						$file = str_replace($replaced_paths, '', $value['file']);
						$line = $value['line'];
						return app(OutputResponseFactory::class)
							->failure('document.model_not_exists', false, [
									'model' => $exception->getModel(),
									'file' => $file ,
									'line' => $line,
									'id' => implode(',', $exception->getIds())
								])
							->setRequest($request);
					}
				}
			}
			else if($exception instanceof PermissionException)
				return app(OutputResponseFactory::class)
					->failure('auth.permission_forbidden')
					->setRequest($request);
			else if ($exception instanceof TokenMismatchException)
				return app(OutputResponseFactory::class)
					->failure('validation.csrf_invalid')
					->setRequest($request);
			else if (($exception instanceof QueryException) || ($exception instanceof PDOException))
				return app(OutputResponseFactory::class)
					->error('server.error_database')
					->setRequest($request);
			// other 500 errors
		}

		return parent::render($request, $exception);
	}

	/**
	 * Prepare a JSON response for the given exception.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception $e
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function prepareJsonResponse($request, Exception $e)
	{
		$status = $this->isHttpException($e) ? $e->getStatusCode() : 500;

		$headers = $this->isHttpException($e) ? $e->getHeaders() : [];

		return app(OutputResponseFactory::class)
			->exception($e)
			->setRequest($request)
			->withHeaders($headers)
			->setStatusCode($status);
	}

	/**
	 * Convert an authentication exception into a response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Illuminate\Auth\AuthenticationException  $exception
	 * @return \Illuminate\Http\Response
	 */
	protected function unauthenticated($request, AuthenticationException $exception)
	{
		return $request->expectsJson()
					//? response()->json(['message' => $exception->getMessage()], 401)
					? app(OutputResponseFactory::class)->failure('auth.unlogin')->setRequest($request)
					: redirect()->guest(route('login'));
	}

}
