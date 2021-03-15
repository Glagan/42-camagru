<?php namespace Camagru\Http;

class Response
{
	const OK = 200;
	const CREATED = 201;
	const ACCEPTED = 202;
	const NO_CONTENT = 204;
	const MOVED_PERMANENTLY = 301;
	const FOUND = 302;
	const PERMANENTLY_REDIRECT = 308; // RFC7238
	const BAD_REQUEST = 400;
	const UNAUTHORIZED = 401;
	const FORBIDDEN = 403;
	const NOT_FOUND = 404;
	const METHOD_NOT_ALLOWED = 405;
	const INTERNAL_SERVER_ERROR = 500;

	protected $compressMethods;
	protected $content;
	protected $headers;
	protected $code;

	public function __construct(Request $request, $content = '', $headers = [], $code = Response::OK)
	{
		$this->compressMethods = $request->getHeaders()->get(Header::ACCEPT_ENCODING);
		$this->content = $content;
		$this->headers = new HeaderList($headers);
		$this->code = $code;
	}

	private function compress()
	{
		$methods = \explode(',', $this->compressMethods);
		$methodUsed = false;
		foreach ($methods as $method) {
			$method = \trim($method);
			if ($method == 'deflate') {
				$methodUsed = 'deflate';
				$this->content = \gzcompress($this->content);
				break;
			} else if ($method == 'gzip') {
				$methodUsed = 'gzip';
				$this->content = \gzencode($this->content);
				break;
			}
		}
		if ($methodUsed) {
			$this->headers->add(Header::CONTENT_ENCODING, $methodUsed);
		}
	}

	public function render()
	{

		// Apply deflate or gzip compression when possible
		if ($this->compressMethods !== false) {
			$this->compress();
		}

		// Content-Length
		$contentLength = \mb_strlen($this->content);
		$this->headers->add(Header::CONTENT_LENGTH, $contentLength);

		// Add headers
		foreach ($this->headers->all() as $key => $value) {
			\header($key . ': ' . $value);
		}
		\http_response_code($this->code);

		// Display content
		echo $this->content;
	}
}
