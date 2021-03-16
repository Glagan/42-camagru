<?php

class ErrorHandler
{
	public static function handle($errno, $errstr, $errfile, $errline)
	{
		Log::debug([
			'errno' => $errno,
			'errstr' => $errstr,
			'errfile' => $errfile,
			'errline' => $errline,
		]);
	}
}
