<?php namespace Exception;

use Camagru\Http\JSONResponse;
use Camagru\Http\Response;

class ValidateException extends \Exception implements HTTPException
{
	protected $reason;

	public function __construct(string $reason = '')
	{
		$this->reason = $reason;
	}

	public function getResponse(string $mode): Response
	{
		return new JSONResponse(['error' => $this->reason], Response::BAD_REQUEST);
	}
}
