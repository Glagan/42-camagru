<?php namespace Camagru\Http;

use Env;

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

	protected $compressMethods = null;
	protected $content;
	protected $headers;
	protected $code;

	public function __construct($content = '', $headers = [], $code = Response::OK, Request $request = null)
	{
		$this->content = $content;
		$this->headers = new HeaderList($headers);
		$this->code = $code;
		if ($request !== null) {
			$this->forRequest($request);
		}
	}

	public function setContent($content): self
	{
		$this->content = $content;
		return $this;
	}

	public function setHeaders(array $headers): self
	{
		$this->headers = new HeaderList($headers);
		return $this;
	}

	public function withHeaders(array $headers): self
	{
		foreach ($headers as $name => $value) {
			$this->headers->add($name, $value);
		}
		return $this;
	}

	public function setCode(int $code): self
	{
		$this->code = $code;
		return $this;
	}

	public function forRequest(Request $request): self
	{
		$this->compressMethods = $request->getHeaders()->get(Header::ACCEPT_ENCODING);
		return $this;
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
		// Convert body to a string if it's an array
		if (\is_array($this->content)) {
			$this->content = \json_encode($this->content);
			$this->headers->add(Header::CONTENT_TYPE, 'application/json; charset=utf-8');
		}

		// Apply deflate or gzip compression when possible
		if (Env::get('Camagru', 'compress', false) && Env::get('Camagru', 'mode') != 'debug' && \is_array($this->compressMethods)) {
			$this->compress();
		}

		// Content-Length
		if (Env::get('Camagru', 'mode') != 'debug') {
			$contentLength = \mb_strlen($this->content);
			$this->headers->add(Header::CONTENT_LENGTH, $contentLength);
		}

		// Add headers
		foreach ($this->headers->all() as $key => $value) {
			\header($key . ': ' . $value);
		}
		\http_response_code($this->code);

		// Display content
		echo $this->content;
	}
}
