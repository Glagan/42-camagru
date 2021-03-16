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

	/**
	 * Returns a JSON Response with $body.
	 *
	 * @param array $body
	 * @param array|null $headers
	 * @param integer $code
	 *
	 * @return Http\Response
	 */
	protected function json($body, $headers = null, $code = Response::OK): Response
	{
		if ($headers !== null) {
			$headers[Header::CONTENT_TYPE] = 'application/json; charset=utf-8';
		} else {
			$headers = [Header::CONTENT_TYPE => 'application/json; charset=utf-8'];
		}
		return new Response($this->request, \json_encode($body), $headers, $code);
	}
}
