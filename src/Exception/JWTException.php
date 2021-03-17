<?php namespace Exception;

use Camagru\Http\Response;
use Log;

class JWTException extends \Exception
{
	protected $token;
	protected $reason;

	public function __construct(string $token, string $reason)
	{
		$this->token = $token;
		$this->reason = $reason;
	}

	public function log()
	{
		Log::debug('Invalid JWT Token:', [
			'token' => $this->token,
			'reason' => $this->reason,
		]);
	}

	public function render()
	{
		$response = new Response(['error' => $this->reason], [], Response::BAD_REQUEST);
		$response->render();
	}
}
