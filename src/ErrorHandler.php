<?php

class ErrorHandler
{
	/**
	 * Log the received Error.
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 * @return void
	 */
	public static function handle(int $errno, string $errstr, string $errfile, int $errline)
	{
		Log::debug([
			'errno' => $errno,
			'errstr' => $errstr,
			'errfile' => $errfile,
			'errline' => $errline,
		]);
	}
}
