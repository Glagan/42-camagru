<?php namespace Exception;

use Camagru\Http\Request;
use Camagru\Http\Response;

class ValidateException extends \Exception
{
	protected $request;
	protected $field;
	protected $validator;
	protected $reason;

	public function __construct(Request $request, string $reason = '')
	{
		$this->request = $request;
		$this->reason = $reason;
	}

	public function render()
	{
		$response = new Response(['error' => $this->reason], [], Response::BAD_REQUEST, $this->request);
		$response->render();
	}
}
