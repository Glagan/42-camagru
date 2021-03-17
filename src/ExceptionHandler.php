<?php

use Camagru\Http\Response;

class ExceptionHandler
{
	public static function handle(\Throwable $ex)
	{
		// Log
		if (\method_exists($ex, 'log')) {
			$ex->log();
		} else {
			Log::error($ex);
		}

		// Render
		if (\method_exists($ex, 'render')) {
			$ex->render();
		} else {
			if (Env::get('camagru', 'mode') == 'debug') {
				$response = new Response(
					[
						'error' => 'Uncatched exception',
						'message' => $ex->getMessage(),
						'code' => $ex->getCode(),
						'file' => $ex->getFile(),
						'line' => $ex->getLine(),
						'trace' => $ex->getTrace(),
					],
					[],
					Response::INTERNAL_SERVER_ERROR
				);
			} else {
				$response = new Response(
					['error' => 'Server error. Retry later.'],
					[],
					Response::INTERNAL_SERVER_ERROR
				);
			}
			$response->render();
		}
	}
}
