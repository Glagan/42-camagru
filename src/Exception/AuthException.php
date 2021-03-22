<?php namespace Exception;

use Camagru\Http\JSONResponse;
use Camagru\Http\Response;

class AuthException extends \Exception implements HTTPException
{
	/**
	 * @var string
	 */
	protected $reason;

	public function __construct(string $reason)
	{
		$this->reason = $reason;
	}

	/**
	 * @param string $mode
	 * @return \Camagru\Http\Response
	 */
	public function getResponse(string $mode): Response
	{
		return new JSONResponse(['error' => $this->reason], Response::UNAUTHORIZED);
	}
}
