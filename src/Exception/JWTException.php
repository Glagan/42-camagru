<?php namespace Exception;

use Camagru\Http\JSONResponse;
use Camagru\Http\Response;
use Log;

class JWTException extends \Exception implements HTTPException, LoggedException
{
	/**
	 * @var string
	 */
	protected $token;
	/**
	 * @var string
	 */
	protected $reason;

	public function __construct(string $token, string $reason)
	{
		$this->token = $token;
		$this->reason = $reason;
	}

	/**
	 * @return void
	 */
	public function log(): void
	{
		Log::debug('Invalid JWT Token:', [
			'token' => $this->token,
			'reason' => $this->reason,
		]);
	}

	/**
	 * @param string $mode
	 * @return \Camagru\Http\Response
	 */
	public function getResponse(string $mode): Response
	{
		return new JSONResponse(['error' => $this->reason], Response::BAD_REQUEST);
	}
}
