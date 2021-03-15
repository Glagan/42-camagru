<?php namespace Camagru;

use Camagru\Http\Header;
use Camagru\Http\Response;

abstract class Controller
{
	protected $request;
	protected $requestHeaders;
	protected $input;

	public function __construct($request)
	{
		$this->request = $request;
		$this->requestHeaders = $request->getHeaders();
		$this->input = $request->getInput();
	}

	protected function json($body, $headers = null, $code = Response::OK)
	{
		if ($headers !== null) {
			$headers[Header::CONTENT_TYPE] = 'application/json; charset=utf-8';
		} else {
			$headers = [Header::CONTENT_TYPE => 'application/json; charset=utf-8'];
		}
		return new Response($this->request, \json_encode($body), $headers, $code);
	}
}
