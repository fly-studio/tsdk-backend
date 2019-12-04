<?php

namespace Plugins\Sdk\App\Http\Controllers;

use Illuminate\Http\Request;

trait LogTrait {

	protected function log($message = null, array $context = [])
	{
		if (is_null($message)) {
            return app('log')->driver('sdk');
        }

        return app('log')->driver('sdk')->debug($message, $context);
	}

	protected function logRequest(Request $request)
	{
		// To do
		// $this->log();
	}
}
