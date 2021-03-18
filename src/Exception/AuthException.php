<?php namespace Exception;

use Camagru\Http\Response;

class AuthException extends \Exception
{
	protected $reason;

	public function __construct(string $reason)
	{
		$this->reason = $reason;
	}

	public function render()
	{
		$response = new Response(['error' => $this->reason], [], Response::UNAUTHORIZED);
		$response->render();
	}
}
