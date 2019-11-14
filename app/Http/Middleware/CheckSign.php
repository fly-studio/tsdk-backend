<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckSign {

    public function handle(Request $request, Closure $next)
    {
    	$sign = $request->input('sign');

    	if (empty($sign))
    		throw new HttpException(302, 'Lost sign.');

    }
}
