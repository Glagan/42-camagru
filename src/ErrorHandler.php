<?php

class ErrorHandler
{
	public static function handle($errno, $errstr, $errfile, $errline)
	{
		echo 'Error: ' . $errno;
	}
}
