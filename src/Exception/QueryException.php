<?php namespace Exception;

use Log;
use SQL\Query;

class QueryException extends \Exception
{
	protected $query;
	protected $reason;

	public function __construct(Query $query, string $reason)
	{
		$this->query = $query;
		$this->reason = $reason;
	}

	public function log()
	{
		Log::debug('Invalid Query', [
			'reason' => $this->reason,
			'query' => $this->query,
		]);
	}
}
