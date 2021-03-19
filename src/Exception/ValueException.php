<?php namespace Exception;

use Log;

class ValueException extends \Exception
{
	protected $reason;

	public function __construct(string $reason)
	{
		$this->reason = $reason;
	}

	public function log()
	{
		Log::debug('Invalid or unsafe Value', [
			'reason' => $this->reason,
		]);
	}
}
