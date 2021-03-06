<?php namespace Exception;

use Log;

class SQLException extends \Exception implements LoggedException
{
	/**
	 * @var \PDOStatement
	 */
	protected $statement;
	/**
	 * @var int
	 */
	protected $sqlStateError;
	/**
	 * @var int
	 */
	protected $driverErrorCode;
	/**
	 * @var string
	 */
	protected $driverErrorMessage;

	public function __construct(\PDOStatement $statement)
	{
		$this->statement = $statement;
		$errorInfo = $statement->errorInfo();
		$this->sqlStateError = $errorInfo[0];
		$this->driverErrorCode = $errorInfo[1];
		$this->driverErrorMessage = $errorInfo[2];
	}

	/**
	 * @return void
	 */
	public function log(): void
	{
		// We need to buffer the output since debugDumpParams doesn't write to a variable
		\ob_start();
		$this->statement->debugDumpParams();
		$statementDump = \trim(\ob_get_contents());
		\ob_end_clean();
		Log::debug('Error while executing query:', [
			'statement' => $statementDump,
			'sqlState' => $this->sqlStateError,
			'driverErrorCode' => $this->driverErrorCode,
			'driverErrorMessage' => $this->driverErrorMessage,
		]);
	}
}
