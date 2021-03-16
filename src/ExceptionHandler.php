<?php

class ExceptionHandler
{
	public static function handle(\Throwable $ex)
	{
		if (\method_exists($ex, 'log')) {
			$ex->log();
		} else {
			Log::error($ex);
		}
	}
}
