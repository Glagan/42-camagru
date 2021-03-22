<?php namespace Exception;

use Log;
use SQL\Query;

class QueryException extends \Exception implements LoggedException
{
	/**
	 * @var \SQL\Query
	 */
	protected $query;
	/**
	 * @var string
	 */
	protected $reason;

	public function __construct(Query $query, string $reason)
	{
		$this->query = $query;
		$this->reason = $reason;
	}

	/**
	 * @return void
	 */
	public function log(): void
	{
		Log::debug('Invalid Query', [
			'reason' => $this->reason,
			'query' => $this->query,
		]);
	}
}
