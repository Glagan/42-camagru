<?php

use Camagru\Http\JSONResponse;
use Camagru\Http\Response;
use Exception\HTTPException;
use Exception\LoggedException;

class ExceptionHandler
{
	public static function handle(\Throwable $ex)
	{
		// Log
		if ($ex instanceof LoggedException) {
			$ex->log();
		} else {
			Log::error($ex);
		}

		// Render
		if ($ex instanceof HTTPException) {
			$response = $ex->getResponse(Env::get('Camagru', 'mode'));
		} else {
			if (Env::get('Camagru', 'mode') == 'debug') {
				$response = new JSONResponse(
					[
						'error' => 'Exception',
						'message' => $ex->getMessage(),
						'code' => $ex->getCode(),
						'file' => $ex->getFile(),
						'line' => $ex->getLine(),
						'trace' => $ex->getTrace(),
					],
					Response::INTERNAL_SERVER_ERROR
				);
			} else {
				$response = new JSONResponse(
					['error' => 'Server error. Retry later.'],
					Response::INTERNAL_SERVER_ERROR
				);
			}
		}
		$response->render();
	}
}
