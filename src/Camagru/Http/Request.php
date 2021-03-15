<?php namespace Camagru\Http;

/**
 * Find and normalize all requested inputs from a received request.
 */
class Request
{
	protected $method;
	protected $uri;
	protected $headers;
	protected $input;

	public function __construct()
	{
		$this->method = \strtoupper(\filter_input(INPUT_SERVER, 'REQUEST_METHOD'));
		$this->uri = \filter_input(INPUT_SERVER, 'REQUEST_URI');
		$this->uri = \trim(\parse_url($this->uri)['path'], '/');
		$this->headers = new HeaderList;
		$headers = \filter_var_array(\apache_request_headers(), FILTER_DEFAULT);
		foreach ($headers as $name => $value) {
			$this->headers->add($name, $value);
		}
		$this->input = new RequestInput($this->method, $this->headers);
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getUri(): string
	{
		return $this->uri;
	}

	public function getLocalUri($basePath): string
	{
		return \str_replace($basePath, '', $this->uri);
	}

	public function getHeaders(): HeaderList
	{
		return $this->headers;
	}

	public function getInput(): RequestInput
	{
		return $this->input;
	}
}
