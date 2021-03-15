<?php

class ExceptionHandler
{
	public static function handle(\Throwable $ex)
	{
		echo 'Exception: ' . $ex->getCode();
	}
}
